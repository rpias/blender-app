<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Obra;
use App\Services\Aberturas\CalculadoraCorredizaS20;
use App\Services\Aberturas\CalculadoraMarcoFijo;
use App\Services\Aberturas\ParametrosFormulario;
use App\Servicios\Exportacion\PresupuestoPdf;
use App\Servicios\Exportacion\XlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function show(Obra $obra, ParametrosFormulario $formulario): View
    {
        $obra->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        $series = $formulario->series(CalculadoraMarcoFijo::TIPOLOGIA);
        $seriesCorrediza = collect($formulario->series(CalculadoraCorredizaS20::TIPOLOGIA))
            ->where('id', 19)
            ->values()
            ->all();
        $serieInicial = $series[0]['id'] ?? null;

        return view('obras.show', [
            'obra' => $obra,
            'series' => $series,
            'seriesCorrediza' => $seriesCorrediza,
            'vidrios' => $formulario->vidrios(),
            'opcionesIniciales' => $serieInicial !== null
                ? $formulario->opciones(CalculadoraMarcoFijo::TIPOLOGIA, $serieInicial)
                : ['perfiles' => [], 'divisores' => [], 'contravidrios' => []],
            'total' => $obra->elementos->sum(fn ($elemento) => (float) ($elemento->aberturaSimple?->precioUnitario ?? 0) * (int) ($elemento->aberturaSimple?->cantidad ?? 0)),
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

        $libro = new XlsxWriter('Presupuesto '.$obra->numero);
        $libro->agregarFila(['Nº', 'Tipología', 'Serie', 'Medidas (mm)', 'Cantidad', 'Ubicación', 'Precio unitario', 'Subtotal'], true);

        $total = 0.0;
        foreach ($obra->elementosPresupuestables() as $elemento) {
            $abertura = $elemento->aberturaSimple;
            $subtotal = (float) ($abertura->precioUnitario ?? 0) * (int) $abertura->cantidad;
            $total += $subtotal;

            $libro->agregarFila([
                (int) $elemento->numero,
                (string) ($abertura->tipologia?->nombre ?? ''),
                (string) ($abertura->serie?->nombre ?? ''),
                number_format((float) $abertura->ancho, 0, '', '').' x '.number_format((float) $abertura->alto, 0, '', ''),
                (int) $abertura->cantidad,
                (string) ($abertura->ubicacion ?? ''),
                (float) ($abertura->precioUnitario ?? 0),
                round($subtotal, 2),
            ]);
        }

        $libro->agregarFila([]);
        $libro->agregarFila(['', '', '', '', '', 'TOTAL', '', round($total, 2)], true);

        $ruta = tempnam(sys_get_temp_dir(), 'alucal-xlsx').'.xlsx';
        $libro->guardar($ruta);

        return response()->download($ruta, 'presupuesto-obra-'.$obra->numero.'.xlsx')->deleteFileAfterSend(true);
    }

    public function exportarCsv(Obra $obra): StreamedResponse
    {
        $obra->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        $nombre = 'presupuesto-obra-'.$obra->numero.'.csv';

        return response()->streamDownload(function () use ($obra) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel respete los acentos

            fputcsv($salida, ['Nº', 'Tipología', 'Serie', 'Medidas (mm)', 'Cantidad', 'Ubicación', 'Precio unitario', 'Subtotal']);

            $total = 0.0;
            foreach ($obra->elementos as $elemento) {
                $abertura = $elemento->aberturaSimple;
                if ($abertura === null) {
                    continue;
                }

                $subtotal = (float) ($abertura->precioUnitario ?? 0) * (int) $abertura->cantidad;
                $total += $subtotal;

                fputcsv($salida, [
                    $elemento->numero,
                    $abertura->tipologia?->nombre,
                    $abertura->serie?->nombre,
                    number_format((float) $abertura->ancho, 0, '', '').' x '.number_format((float) $abertura->alto, 0, '', ''),
                    $abertura->cantidad,
                    $abertura->ubicacion,
                    number_format((float) ($abertura->precioUnitario ?? 0), 2, '.', ''),
                    number_format($subtotal, 2, '.', ''),
                ]);
            }

            fputcsv($salida, []);
            fputcsv($salida, ['', '', '', '', '', 'TOTAL', '', number_format($total, 2, '.', '')]);

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
