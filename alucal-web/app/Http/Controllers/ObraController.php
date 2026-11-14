<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Obra;
use App\Models\Perfil;
use App\Services\Aberturas\CalculadoraBatiente;
use App\Services\Aberturas\CalculadoraCorredizaS20;
use App\Services\Aberturas\CalculadoraMarcoFijo;
use App\Services\Aberturas\CalcularVano;
use App\Services\Aberturas\ParametrosFormulario;
use App\Servicios\Exportacion\LineasPresupuesto;
use App\Servicios\Exportacion\PresupuestoPdf;
use App\Servicios\Exportacion\XlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ObraController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $obras = Obra::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('cliente', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('fechaCreacion')
            ->paginate(20)
            ->withQueryString();

        return view('obras.index', compact('obras', 'buscar'));
    }

    public function create(): View
    {
        return view('obras.create', [
            'clientes' => Cliente::orderBy('nombre')->get(),
            'proximoNumero' => (int) (Obra::max('id') ?? 0) + 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'numero' => ['required', 'string', 'max:10', 'unique:obra,numero'],
            'nombre' => ['required', 'string', 'max:255'],
            'cliente' => ['nullable', 'string', 'max:160'],
            'arquitecto' => ['nullable', 'string', 'max:50'],
            'ubicacion' => ['nullable', 'string', 'max:50'],
            'estadoSeguimiento' => ['nullable', 'string', 'max:10'],
        ]);

        $datos['creador'] = $request->user()?->name;
        $datos['fechaCreacion'] = now()->toDateString();
        $datos['fechaUltModificacion'] = now()->toDateString();

        $obra = Obra::create($datos);

        return redirect()
            ->route('obras.show', $obra)
            ->with('estado', 'Obra creada correctamente.');
    }

    public function edit(Obra $obra): View
    {
        return view('obras.edit', [
            'obra' => $obra,
            'clientes' => Cliente::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Obra $obra): RedirectResponse
    {
        $datos = $request->validate([
            'numero' => ['required', 'string', 'max:20', Rule::unique('obra', 'numero')->ignore($obra->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'arquitecto' => ['nullable', 'string', 'max:120'],
            'fechaCreacion' => ['nullable', 'date'],
            'estadoSeguimiento' => ['nullable', 'string', 'in:ABIERTA,CERRADA'],
            'proximaAccionFecha' => ['nullable', 'date'],
            'proximaAccionDetalle' => ['nullable', 'string', 'max:180'],
            'idCliente' => ['nullable', 'integer', 'exists:clientes,id'],
        ]);

        $obra->update($datos);

        return redirect()
            ->route('obras.show', $obra)
            ->with('estado', 'Obra actualizada.');
    }

    public function destroy(Obra $obra): RedirectResponse
    {
        $numero = $obra->numero;

        // Los elementos cuelgan de la obra; se borran antes para no dejar huérfanos.
        $obra->elementos()->delete();
        $obra->delete();

        return redirect()
            ->route('obras.index')
            ->with('estado', "Obra {$numero} eliminada.");
    }

    public function show(Obra $obra, ParametrosFormulario $formulario): View
    {
        $obra->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        $series = $formulario->series(CalculadoraMarcoFijo::TIPOLOGIA);
        $seriesBatiente = $formulario->series(CalculadoraBatiente::TIPOLOGIA);
        $seriesCorrediza = collect($formulario->series(CalculadoraCorredizaS20::TIPOLOGIA))
            ->where('id', 19)
            ->values()
            ->all();
        $serieInicial = $series[0]['id'] ?? null;

        $candidatos = $obra->elementos
            ->filter(fn ($elemento) => $elemento->aberturaSimple !== null && $elemento->idElementoVano === null)
            ->values();

        $vanos = $obra->elementos
            ->filter(fn ($elemento) => $elemento->tipoElemento === 'ABERTURA_COMPUESTA'
                && $elemento->hijos()->exists())
            ->values();

        return view('obras.show', [
            'obra' => $obra,
            'series' => $series,
            'seriesBatiente' => $seriesBatiente,
            'seriesCorrediza' => $seriesCorrediza,
            'vidrios' => $formulario->vidrios(),
            'opcionesIniciales' => $serieInicial !== null
                ? $formulario->opciones(CalculadoraMarcoFijo::TIPOLOGIA, $serieInicial)
                : ['perfiles' => [], 'divisores' => [], 'contravidrios' => []],
            'candidatos' => $candidatos,
            'vanos' => $vanos,
            'conectores' => Perfil::query()->orderBy('Numero')->get(['Numero', 'Descripcion']),
            'total' => $this->totalObra($obra),
        ]);
    }

    /**
     * Exportación CSV del presupuesto de la obra (sin dependencias externas).
     *
     * El PDF/XLSX con el formato del original queda para cuando se aprueben
     * dompdf y PhpSpreadsheet.
     */

    /**
     * Presupuesto de la obra en PDF (Dompdf, sin binarios externos).
     */
    /**
     * Total de la obra: aberturas sueltas más vanos compuestos (spec 0009).
     */
    private function totalObra(Obra $obra): float
    {
        $total = 0.0;
        $calcular = app(CalcularVano::class);

        foreach ($obra->elementos as $elemento) {
            if ($elemento->tipoElemento === 'ABERTURA_COMPUESTA') {
                $total += $calcular->detalle($elemento)['total'];

                continue;
            }

            $total += (float) ($elemento->aberturaSimple?->precioUnitario ?? 0) * (int) ($elemento->aberturaSimple?->cantidad ?? 0);
        }

        return $total;
    }

    public function exportarPdf(Obra $obra, PresupuestoPdf $pdf): Response
    {
        $contenido = $pdf->generar($obra);

        return response($contenido, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="presupuesto-obra-'.$obra->numero.'.pdf"',
        ]);
    }

    /**
     * Presupuesto de la obra en XLSX (escritor propio, sin dependencias).
     */
    public function exportarXlsx(Obra $obra): BinaryFileResponse
    {
        $obra->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        $lineas = (new LineasPresupuesto)->para($obra);

        $libro = new XlsxWriter('Presupuesto '.$obra->numero);
        $libro->agregarFila(['Nº', 'Detalle', 'Medidas / conector', 'Cantidad', 'Ubicación', 'Terminación', 'Precio unitario', 'Subtotal'], true);

        foreach ($lineas as $linea) {
            $libro->agregarFila([
                $linea['numero'],
                $linea['descripcion'],
                $linea['medidas'],
                $linea['tipo'] === 'vano' ? '' : $linea['cantidad'],
                $linea['ubicacion'],
                $linea['terminacion'],
                $linea['unitario'] !== null ? $linea['unitario'] : '',
                round($linea['subtotal'], 2),
            ], $linea['tipo'] === 'vano');

            foreach ($linea['hijos'] as $hijo) {
                $libro->agregarFila([
                    $hijo['numero'], '   ↳ '.$hijo['descripcion'], $hijo['medidas'], '', '', '', '',
                    round($hijo['subtotal'], 2),
                ]);
            }
        }

        $libro->agregarFila([]);
        $libro->agregarFila(['', '', '', '', '', 'TOTAL', '', round((new LineasPresupuesto)->total($lineas), 2)], true);

        $ruta = tempnam(sys_get_temp_dir(), 'alucal-xlsx').'.xlsx';
        $libro->guardar($ruta);

        return response()->download($ruta, 'presupuesto-obra-'.$obra->numero.'.xlsx')->deleteFileAfterSend(true);
    }

    public function exportarCsv(Obra $obra): StreamedResponse
    {
        $obra->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        $nombre = 'presupuesto-obra-'.$obra->numero.'.csv';

        $lineas = (new LineasPresupuesto)->para($obra);

        return response()->streamDownload(function () use ($lineas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel respete los acentos

            fputcsv($salida, ['Nº', 'Detalle', 'Medidas / conector', 'Cantidad', 'Ubicación', 'Terminación', 'Precio unitario', 'Subtotal']);

            foreach ($lineas as $linea) {
                fputcsv($salida, [
                    $linea['numero'],
                    $linea['descripcion'],
                    $linea['medidas'],
                    $linea['tipo'] === 'vano' ? '' : $linea['cantidad'],
                    $linea['ubicacion'],
                    $linea['terminacion'],
                    $linea['unitario'] !== null ? number_format($linea['unitario'], 2, '.', '') : '',
                    number_format($linea['subtotal'], 2, '.', ''),
                ]);

                foreach ($linea['hijos'] as $hijo) {
                    fputcsv($salida, [
                        $hijo['numero'], '   ↳ '.$hijo['descripcion'], $hijo['medidas'], '', '', '', '',
                        number_format($hijo['subtotal'], 2, '.', ''),
                    ]);
                }
            }

            fputcsv($salida, []);
            fputcsv($salida, ['', '', '', '', '', 'TOTAL', '', number_format((new LineasPresupuesto)->total($lineas), 2, '.', '')]);

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
