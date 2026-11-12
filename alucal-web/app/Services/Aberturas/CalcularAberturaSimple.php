<?php

namespace App\Services\Aberturas;

use App\Models\AberturaSimple;
use App\Models\VidrioSinCamara;
use App\Services\Precios\AberturaParaPrecio;
use App\Services\Precios\CalculadoraPrecioAbertura;
use App\Services\Precios\ResultadoPrecio;

/**
 * Orquesta el cálculo de una abertura simple: líneas físicas → precio → snapshot.
 *
 * El original recalcula al leer (DaoAberturaSimple.java:223); acá se guarda el
 * resultado en la propia fila (columnas lineas/precioUnitario) para que el
 * presupuesto sea reproducible sin recalcular.
 */
class CalcularAberturaSimple
{
    public function __construct(
        private readonly CalculadoraMarcoFijo $marcoFijo,
        private readonly CalculadoraCorredizaS20 $corrediza,
        private readonly CalculadoraCorredizaDomina $domina,
        private readonly CalculadoraCorredizaHA62 $ha62,
        private readonly CalculadoraCorredizaS25 $s25,
        private readonly CalculadoraBatiente $batiente,
        private readonly CalculadoraCorredizaDoppia $doppia,
        private readonly CalculadoraCorredizaDoppia22 $doppia22,
        private readonly CalculadoraCorredizaNordical $nordical,
        private readonly CalculadoraProyectanteHA62 $proyectante,
        private readonly CalculadoraPrecioAbertura $precios,
    ) {}

    /**
     * @param  array{
     *     perfilMarco: string,
     *     perfilContravidrio?: string|null,
     *     perfilDivisor?: string|null,
     *     divisoresHorizontales?: int,
     *     divisoresVerticales?: int,
     *     vidrioId?: int|null
     * }  $parametros
     */
    public function ejecutar(AberturaSimple $abertura, array $parametros): ResultadoPrecio
    {
        $lineas = $this->lineas($abertura, $parametros);

        $resultado = $this->precios->calcular(new AberturaParaPrecio(
            anchoMM: (float) $abertura->ancho,
            altoMM: (float) $abertura->alto,
            idTipologia: (int) $abertura->idTipologia,
            idSerie: (int) $abertura->idSerie,
            terminacion: (string) ($abertura->terminacion ?: 'PINTADO_BLANCO'),
            cantidad: max(1, (int) $abertura->cantidad),
            barras: array_map(fn (array $barra) => [
                'pesoKgPorMetro' => $barra['pesoKgPorMetro'],
                'cantidad' => $barra['cantidad'],
                'largoMM' => $barra['largoMM'],
                'parte' => $barra['parte'],
            ], $lineas->barras),
            accesorios: $lineas->accesorios,
            vidrios: $lineas->vidrios,
        ));

        $abertura->forceFill([
            'parametros' => $parametros,
            'lineas' => [
                'barras' => $lineas->barras,
                'accesorios' => $lineas->accesorios,
                'vidrios' => $lineas->vidrios,
                'kilos' => round($lineas->kilos(), 3),
            ],
            'precioUnitario' => $resultado->precio,
            'calculadoEn' => now(),
        ])->save();

        return $resultado;
    }

    public function lineas(AberturaSimple $abertura, array $parametros): LineasAbertura
    {
        $idTipologia = (int) $abertura->idTipologia;
        $idSerie = (int) $abertura->idSerie;

        if ($idTipologia === CalculadoraCorredizaS20::TIPOLOGIA && $idSerie === 2) {
            return $this->domina->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'idSerie' => $idSerie,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'enganche' => (string) ($parametros['enganche'] ?? 'SIMPLE'),
                'ruedaCodigo' => (string) ($parametros['ruedaCodigo'] ?? '193155'),
                'divisorAlMedio' => (bool) ($parametros['divisorAlMedio'] ?? false),
                'espesorVidrio' => (int) ($parametros['espesorVidrio'] ?? 4),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraProyectanteHA62::TIPOLOGIA) {
            return $this->proyectante->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'contravidrios' => (string) ($parametros['contravidrios'] ?? 'RECTOS'),
                'colorHerrajes' => (string) ($parametros['colorHerrajes'] ?? $abertura->colorHerrajes ?? 'BLANCO'),
                'espesorVidrio' => (int) ($parametros['espesorVidrio'] ?? 4),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraCorredizaNordical::TIPOLOGIA) {
            return $this->nordical->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'cantHojasCorredizas' => (int) ($parametros['cantHojasCorredizas'] ?? 1),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraCorredizaDoppia22::TIPOLOGIA) {
            return $this->doppia22->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'marcoInferior' => (string) ($parametros['marcoInferior'] ?? 'ADR 2494'),
                'paranteLateral' => (string) ($parametros['paranteLateral'] ?? 'ADR 2474'),
                'zocalo' => (string) ($parametros['zocalo'] ?? 'ADR 2204'),
                'enganche' => (string) ($parametros['enganche'] ?? 'SIMPLE'),
                'enganchePerfil' => $parametros['enganchePerfil'] ?? 'ADR 2479',
                'colorHerrajes' => (string) ($parametros['colorHerrajes'] ?? $abertura->colorHerrajes ?? 'BLANCO'),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraCorredizaDoppia::TIPOLOGIA) {
            return $this->doppia->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'marcoInferior' => (string) ($parametros['marcoInferior'] ?? 'ADR 2494'),
                'marcoSuperior' => (string) ($parametros['marcoSuperior'] ?? 'ADR 2494'),
                'paranteLateral' => (string) ($parametros['paranteLateral'] ?? 'ADR 2474'),
                'zocalo' => (string) ($parametros['zocalo'] ?? 'ADR 2204'),
                'cabezal' => (string) ($parametros['cabezal'] ?? 'ADR 2204'),
                'enganche' => (string) ($parametros['enganche'] ?? 'SIMPLE'),
                'enganchePerfil' => $parametros['enganchePerfil'] ?? 'ADR 2479',
                'colorHerrajes' => (string) ($parametros['colorHerrajes'] ?? $abertura->colorHerrajes ?? 'BLANCO'),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraBatiente::TIPOLOGIA) {
            return $this->batiente->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'cantHojas' => (int) ($parametros['cantHojas'] ?? 1),
                'hoja' => (string) ($parametros['hoja'] ?? 'RECTA'),
                'contravidrios' => (string) ($parametros['contravidrios'] ?? 'RECTOS'),
                'cierre' => (string) ($parametros['cierre'] ?? 'FALLEBA'),
                'colorHerrajes' => (string) ($parametros['colorHerrajes'] ?? 'BLANCO'),
                'espesorVidrio' => (int) ($parametros['espesorVidrio'] ?? 4),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraCorredizaS20::TIPOLOGIA && $idSerie === 20) {
            return $this->s25->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'idSerie' => $idSerie,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'marcoLateral' => (string) ($parametros['marcoLateral'] ?? '2501'),
                'cajaDeAgua' => (bool) ($parametros['cajaDeAgua'] ?? false),
                'paranteLateral' => (string) ($parametros['paranteLateral'] ?? '2544'),
                'zocalo' => (string) ($parametros['zocalo'] ?? '4503'),
                'cabezal' => (string) ($parametros['cabezal'] ?? '4503'),
                'enganche' => (string) ($parametros['enganche'] ?? 'SIMPLE'),
                'enganchePerfil' => $parametros['enganchePerfil'] ?? null,
                'punzonadoManual' => (bool) ($parametros['punzonadoManual'] ?? false),
                'tipoRueda' => (string) ($parametros['tipoRueda'] ?? 'RULEMAN'),
                'divisorAlMedio' => (bool) ($parametros['divisorAlMedio'] ?? false),
                'espesorVidrio' => (int) ($parametros['espesorVidrio'] ?? 4),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraCorredizaS20::TIPOLOGIA && $idSerie === 10) {
            return $this->ha62->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'idSerie' => $idSerie,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'marco' => (string) ($parametros['marco'] ?? '45 GRADOS'),
                'enganche' => (string) ($parametros['enganche'] ?? 'SIMPLE'),
                'espesorVidrio' => (int) ($parametros['espesorVidrio'] ?? 4),
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null),
            ]);
        }

        if ($idTipologia === CalculadoraCorredizaS20::TIPOLOGIA) {
            return $this->corrediza->calcular([
                'anchoMM' => (float) $abertura->ancho,
                'altoMM' => (float) $abertura->alto,
                'idSerie' => $idSerie,
                'cantidad' => max(1, (int) $abertura->cantidad),
                'marcoLateral' => (string) $parametros['marcoLateral'],
                'marcoInferior' => (string) $parametros['marcoInferior'],
                'cajaDeAgua' => (bool) ($parametros['cajaDeAgua'] ?? false),
                'zocalo' => (string) $parametros['zocalo'],
                'cabezal' => (string) $parametros['cabezal'],
                'enganche' => (string) $parametros['enganche'],
                'punzonadoManual' => (bool) ($parametros['punzonadoManual'] ?? false),
                'cantEnganches' => (int) ($parametros['cantEnganches'] ?? 2),
                'divisorAlMedio' => (bool) ($parametros['divisorAlMedio'] ?? false),
                'divisor' => $parametros['divisor'] ?? null,
                'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null),
            ]);
        }

        if ($idTipologia !== CalculadoraMarcoFijo::TIPOLOGIA) {
            throw new SerieSinConstantes(
                "La tipología {$idTipologia} todavía no está portada. ".
                'Disponibles: Marco Fijo (6) y Corrediza S20 (3).'
            );
        }

        return $this->marcoFijo->calcular([
            'anchoMM' => (float) $abertura->ancho,
            'altoMM' => (float) $abertura->alto,
            'idSerie' => $idSerie,
            'cantidad' => max(1, (int) $abertura->cantidad),
            'perfilMarco' => (string) $parametros['perfilMarco'],
            'perfilContravidrio' => $parametros['perfilContravidrio'] ?? null,
            'perfilDivisor' => $parametros['perfilDivisor'] ?? null,
            'divisoresHorizontales' => (int) ($parametros['divisoresHorizontales'] ?? 0),
            'divisoresVerticales' => (int) ($parametros['divisoresVerticales'] ?? 0),
            'vidrio' => $this->vidrio($parametros['vidrioId'] ?? null, $parametros['vidrioDvh'] ?? null),
        ]);
    }

    /**
     * Resuelve el vidrio de una abertura: simple o DVH (exterior + cámara + interior).
     *
     * @param  array{ext: int, camara: int, int: int}|null  $dvh
     * @return array<string, mixed>|null
     */
    private function vidrio(?int $id, ?array $dvh = null): ?array
    {
        if ($dvh !== null) {
            $exterior = VidrioSinCamara::query()->find($dvh['ext']);
            $interior = VidrioSinCamara::query()->find($dvh['int']);

            if ($exterior === null || $interior === null) {
                return null;
            }

            $camara = $dvh['camara'];
            $costoCamara = 0.0;

            try {
                $costoCamara = $this->precios->coeficiente('COSTO_POR_METRO_CAMARA_'.$camara, 0.0);
            } catch (\Throwable) {
                $costoCamara = 0.0;
            }

            return [
                'tipo' => 'dvh',
                'extM2' => (float) $exterior->precio,
                'intM2' => (float) $interior->precio,
                'camaraPorMetro' => $costoCamara,
            ];
        }

        if ($id === null) {
            return null;
        }

        $vidrio = VidrioSinCamara::query()->find($id);

        if ($vidrio === null) {
            return null;
        }

        return [
            'tipo' => 'simple',
            'costoM2' => (float) $vidrio->precio,
            'precioMinimo' => (float) $vidrio->precioMinimo,
            'margen' => (float) $vidrio->margen,
        ];
    }
}
