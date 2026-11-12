<?php

namespace App\Http\Controllers;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Services\Aberturas\CalculadoraBatiente;
use App\Services\Aberturas\CalculadoraCorredizaDoppia;
use App\Services\Aberturas\CalculadoraCorredizaDoppia22;
use App\Services\Aberturas\CalculadoraCorredizaNordical;
use App\Services\Aberturas\CalculadoraCorredizaS20;
use App\Services\Aberturas\CalculadoraMarcoFijo;
use App\Services\Aberturas\CalcularAberturaSimple;
use App\Services\Aberturas\ParametrosFormulario;
use App\Services\Aberturas\SerieSinConstantes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AberturaController extends Controller
{
    public function store(Request $request, Obra $obra, CalcularAberturaSimple $calculadora): RedirectResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', 'string', 'in:ABERTURA_SIMPLE'],
            'idTipologia' => ['required', 'integer', 'in:'.CalculadoraBatiente::TIPOLOGIA.','.CalculadoraCorredizaDoppia::TIPOLOGIA.','.CalculadoraCorredizaDoppia22::TIPOLOGIA.','.CalculadoraCorredizaNordical::TIPOLOGIA.','.CalculadoraMarcoFijo::TIPOLOGIA.','.CalculadoraCorredizaS20::TIPOLOGIA],
            'idSerie' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:999'],
            'ancho' => ['required', 'numeric', 'min:100', 'max:10000'],
            'alto' => ['required', 'numeric', 'min:100', 'max:10000'],
            'ubicacion' => ['nullable', 'string', 'max:60'],
            'terminacion' => ['nullable', 'string', 'max:60'],
            'vidrioId' => ['nullable', 'integer'],

            // Marco fijo
            'perfilMarco' => ['nullable', 'string', 'max:30'],
            'perfilContravidrio' => ['nullable', 'string', 'max:30'],
            'perfilDivisor' => ['nullable', 'string', 'max:30'],
            'divisoresHorizontales' => ['nullable', 'integer', 'min:0', 'max:6'],
            'divisoresVerticales' => ['nullable', 'integer', 'min:0', 'max:6'],

            // Corrediza S20
            'marcoLateral' => ['nullable', 'string', 'in:205,235,291'],
            'marcoInferior' => ['nullable', 'string', 'in:204,236'],
            'cajaDeAgua' => ['nullable', 'boolean'],
            'zocalo' => ['nullable', 'string', 'in:2210,2211,201'],
            'cabezal' => ['nullable', 'string', 'in:2210,2211,201'],
            'enganche' => ['nullable', 'string', 'max:10'],
            'punzonadoManual' => ['nullable', 'boolean'],
            'cantEnganches' => ['nullable', 'integer', 'min:1', 'max:6'],
            'divisorAlMedio' => ['nullable', 'boolean'],
            'engancheDomina' => ['nullable', 'string', 'in:SIMPLE,REFORZADO,DOBLEMENTE REFORZADO'],
            'marcoHA62' => ['nullable', 'string', 'in:45 GRADOS,90 GRADOS'],
            'engancheHA62' => ['nullable', 'string', 'in:SIMPLE,REFORZADO,DOBLEMENTE REFORZADO'],
            'divisor' => ['nullable', 'string', 'max:30'],

            // Batiente
            'cantHojasBatiente' => ['nullable', 'integer', 'in:1,2'],
            'hojaBatiente' => ['nullable', 'string', 'in:RECTA,CURVA'],
            'contravidriosBatiente' => ['nullable', 'string', 'in:CURVOS,RECTOS'],
            'cierreBatiente' => ['nullable', 'string', 'in:FALLEBA,ALDABILLA'],
            'colorHerrajes' => ['nullable', 'string', 'in:NEGRO,BLANCO'],

            // Corrediza 2+1 (Doppia)
            'marcoInferiorDoppia' => ['nullable', 'string', 'in:ADR 2494,ADR 2496'],
            'marcoSuperiorDoppia' => ['nullable', 'string', 'in:ADR 2494,ADR 2496'],
            'paranteDoppia' => ['nullable', 'string', 'in:ADR 2474,ADR 2479'],
            'zocaloDoppia' => ['nullable', 'string', 'in:ADR 2204,ADR 2209'],
            'engancheDoppia' => ['nullable', 'string', 'in:SIMPLE,REFORZADO,DOBLEMENTE REFORZADO'],

            // Corrediza con paño fijo Nórdical
            'cantHojasNordical' => ['nullable', 'integer', 'in:1,2'],
        ]);

        $idTipologia = (int) $datos['idTipologia'];

        $esCorrediza = $idTipologia === CalculadoraCorredizaS20::TIPOLOGIA;
        $serie = (int) $datos['idSerie'];

        $esBatiente = $idTipologia === CalculadoraBatiente::TIPOLOGIA;
        $esDoppia = $idTipologia === CalculadoraCorredizaDoppia::TIPOLOGIA;
        $esDoppia22 = $idTipologia === CalculadoraCorredizaDoppia22::TIPOLOGIA;
        $esNordical = $idTipologia === CalculadoraCorredizaNordical::TIPOLOGIA;

        $parametros = match (true) {
            $esNordical => [
                'cantHojasCorredizas' => (int) ($datos['cantHojasNordical'] ?? 1),
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            $esDoppia22 => [
                'marcoInferior' => (string) ($datos['marcoInferiorDoppia'] ?? 'ADR 2494'),
                'paranteLateral' => (string) ($datos['paranteDoppia'] ?? 'ADR 2474'),
                'zocalo' => (string) ($datos['zocaloDoppia'] ?? 'ADR 2204'),
                'enganche' => (string) ($datos['engancheDoppia'] ?? 'SIMPLE'),
                'enganchePerfil' => 'ADR 2479',
                'colorHerrajes' => (string) ($datos['colorHerrajes'] ?? 'BLANCO'),
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            $esDoppia => [
                'marcoInferior' => (string) ($datos['marcoInferiorDoppia'] ?? 'ADR 2494'),
                'marcoSuperior' => (string) ($datos['marcoSuperiorDoppia'] ?? 'ADR 2494'),
                'paranteLateral' => (string) ($datos['paranteDoppia'] ?? 'ADR 2474'),
                'zocalo' => (string) ($datos['zocaloDoppia'] ?? 'ADR 2204'),
                'cabezal' => (string) ($datos['zocaloDoppia'] ?? 'ADR 2204'),
                'enganche' => (string) ($datos['engancheDoppia'] ?? 'SIMPLE'),
                'enganchePerfil' => 'ADR 2479',
                'colorHerrajes' => (string) ($datos['colorHerrajes'] ?? 'BLANCO'),
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            $esBatiente => [
                'cantHojas' => (int) ($datos['cantHojasBatiente'] ?? 1),
                'hoja' => (string) ($datos['hojaBatiente'] ?? 'RECTA'),
                'contravidrios' => (string) ($datos['contravidriosBatiente'] ?? 'RECTOS'),
                'cierre' => (string) ($datos['cierreBatiente'] ?? 'FALLEBA'),
                'colorHerrajes' => (string) ($datos['colorHerrajes'] ?? 'BLANCO'),
                'espesorVidrio' => 4,
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            $esCorrediza && $serie === 10 => [
                'marco' => (string) ($datos['marcoHA62'] ?? '45 GRADOS'),
                'enganche' => (string) ($datos['engancheHA62'] ?? 'SIMPLE'),
                'espesorVidrio' => 4,
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            $esCorrediza && $serie === 2 => [
                'enganche' => (string) ($datos['engancheDomina'] ?? 'SIMPLE'),
                'ruedaCodigo' => '193155',
                'divisorAlMedio' => (bool) ($datos['divisorAlMedio'] ?? false),
                'espesorVidrio' => 4,
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            $esCorrediza => [
                'marcoLateral' => (string) ($datos['marcoLateral'] ?? '235'),
                'marcoInferior' => (string) ($datos['marcoInferior'] ?? '204'),
                'cajaDeAgua' => (bool) ($datos['cajaDeAgua'] ?? false),
                'zocalo' => (string) ($datos['zocalo'] ?? '2210'),
                'cabezal' => (string) ($datos['cabezal'] ?? '2210'),
                'enganche' => (string) ($datos['enganche'] ?? '203'),
                'punzonadoManual' => (bool) ($datos['punzonadoManual'] ?? false),
                'cantEnganches' => (int) ($datos['cantEnganches'] ?? 2),
                'divisorAlMedio' => (bool) ($datos['divisorAlMedio'] ?? false),
                'divisor' => $datos['divisor'] ?? null,
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
            default => [
                'perfilMarco' => (string) ($datos['perfilMarco'] ?? ''),
                'perfilContravidrio' => $datos['perfilContravidrio'] ?? null,
                'perfilDivisor' => $datos['perfilDivisor'] ?? null,
                'divisoresHorizontales' => (int) ($datos['divisoresHorizontales'] ?? 0),
                'divisoresVerticales' => (int) ($datos['divisoresVerticales'] ?? 0),
                'vidrioId' => $datos['vidrioId'] ?? null,
            ],
        };

        if ($idTipologia === CalculadoraMarcoFijo::TIPOLOGIA && empty($datos['perfilMarco'])) {
            return back()->withInput()->withErrors(['perfilMarco' => 'Elegí el perfil de marco.']);
        }

        try {
            $abertura = DB::transaction(function () use ($obra, $datos, $parametros, $idTipologia, $calculadora) {
                $elemento = ElementoObra::query()->create([
                    'tipoElemento' => 'ABERTURA_SIMPLE',
                    'idObra' => $obra->id,
                    'numero' => (int) (ElementoObra::query()->where('idObra', $obra->id)->max('numero') ?? 0) + 1,
                ]);

                $abertura = AberturaSimple::query()->create([
                    'cantidad' => (int) $datos['cantidad'],
                    'ancho' => (float) $datos['ancho'],
                    'alto' => (float) $datos['alto'],
                    'idTipologia' => $idTipologia,
                    'idSerie' => (int) $datos['idSerie'],
                    'terminacion' => $datos['terminacion'] ?? null,
                    'numero' => $elemento->numero,
                    'colorHerrajes' => '',
                    'colorPlasticos' => '',
                    'ubicacion' => (string) ($datos['ubicacion'] ?? ''),
                    'tipo' => $idTipologia === CalculadoraCorredizaS20::TIPOLOGIA ? 'CORREDIZA' : 'FIJO',
                    'idElementoObra' => $elemento->id,
                    'idRellenoVano' => 1,
                ]);

                $calculadora->ejecutar($abertura, $parametros);

                return $abertura;
            });
        } catch (SerieSinConstantes $error) {
            return back()
                ->withInput()
                ->withErrors(['calculo' => $error->getMessage()]);
        }

        return redirect()
            ->route('obras.show', $obra)
            ->with('estado', 'Abertura agregada: '.number_format((float) $abertura->precioUnitario, 0, ',', '.').' U$S por unidad.');
    }

    public function recalcular(AberturaSimple $abertura, CalcularAberturaSimple $calculadora): RedirectResponse
    {
        try {
            $calculadora->ejecutar($abertura, (array) $abertura->parametros);
        } catch (SerieSinConstantes $error) {
            return back()->withErrors(['calculo' => $error->getMessage()]);
        }

        return back()->with('estado', 'Abertura recalculada.');
    }

    public function destroy(AberturaSimple $abertura): RedirectResponse
    {
        $obraId = $abertura->elemento?->idObra;
        $abertura->elemento?->delete();
        $abertura->delete();

        return $obraId !== null
            ? redirect()->route('obras.show', $obraId)->with('estado', 'Abertura eliminada.')
            : back()->with('estado', 'Abertura eliminada.');
    }

    public function opciones(int $tipologia, int $serie, ParametrosFormulario $formulario): JsonResponse
    {
        return response()->json($formulario->opciones($tipologia, $serie));
    }
}
