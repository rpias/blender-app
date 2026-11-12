<?php

namespace App\Services\Precios;

use RuntimeException;

/**
 * Motor de precio por margen operativo (criterio MARGEN_OPERATIVO).
 *
 * Port de services/precios/ServiceCalcularPrecio_MargenOperativo.java.
 * Fórmulas y orden de redondeo replicados según
 * Alucal_v5.5/_analysis/precios.md.
 */
class CalculadoraPrecioAbertura
{
    public function __construct(private readonly Coeficientes $coeficientes) {}

    public function calcular(AberturaParaPrecio $abertura): ResultadoPrecio
    {
        $margenes = $this->coeficientes->margenes($abertura->idTipologia, $abertura->idSerie);
        $precioKilo = $this->coeficientes->precioPorKilo($abertura->terminacion, $abertura->rpt);

        [$kgMH, $kgMosq] = $this->kilosPorParte($abertura);
        [$accMH, $accMosq] = $this->accesoriosPorParte($abertura);

        $precioMH = ($kgMH * $margenes['despMH'] * $precioKilo + $accMH) * $margenes['margenMH'];
        $precioMosq = ($kgMosq * $margenes['despMosq'] * $precioKilo + $accMosq) * $margenes['margenMosq'];

        $precioVidrio = 0.0;
        $m2Vidrio = 0.0;
        $margenPorM2 = 0.0;

        foreach ($abertura->vidrios as $vidrio) {
            $m2PorPieza = $vidrio['anchoM'] * $vidrio['altoM'];

            if ($vidrio['tipo'] === 'simple') {
                $costoM2 = (float) ($vidrio['costoM2'] ?? 0.0);
                $minimo = (float) ($vidrio['precioMinimo'] ?? 0.0);
                $margen = (float) ($vidrio['margen'] ?? 1.0);
                $precioPieza = max($m2PorPieza * $costoM2 * $margen, $minimo);
                $precioVidrio += $precioPieza * $vidrio['cantidad'];
                $margenPorM2 += $m2PorPieza * $vidrio['cantidad'] * $margen;
                $m2Vidrio += $m2PorPieza * $vidrio['cantidad'];

                continue;
            }

            if ($vidrio['tipo'] !== 'dvh') {
                throw new RuntimeException("No se conoce la clase de vidrio '{$vidrio['tipo']}'.");
            }

            $coefDespVidrio = $this->coeficientes->coeficiente('COEF_DESP_VIDRIO', 1.2);
            $margenFabricante = $this->coeficientes->coeficiente('MARGEN_DVH_FABRICANTE', 1.0);
            $margenCarpinteria = $this->coeficientes->coeficiente('MARGEN_DVH_CARPINTERIA', 1.22);

            $costoExt = (float) ($vidrio['extM2'] ?? 0.0) * $coefDespVidrio * $m2PorPieza;
            $costoInt = (float) ($vidrio['intM2'] ?? 0.0) * $coefDespVidrio * $m2PorPieza;
            $costoCamara = (float) ($vidrio['camaraPorMetro'] ?? 0.0) * ($vidrio['anchoM'] + $vidrio['altoM']) * 2.0;

            $paraFabricante = $costoExt + $costoInt + $costoCamara;

            $precioVidrio += $paraFabricante * $margenFabricante * $margenCarpinteria * $vidrio['cantidad'];
            $m2Vidrio += $m2PorPieza * $vidrio['cantidad'] * 2.0;
        }

        $precioConectores = 0.0;
        foreach ($abertura->barras as $barra) {
            if ($barra['parte'] !== 'CONECTOR') {
                continue;
            }

            $kg = $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];
            $precioConectores += $kg
                * $this->coeficientes->coeficiente('COEF_DESPERDICIO_CONECTORES', 1.1)
                * $precioKilo
                * $this->coeficientes->coeficiente('MARGEN_OPERATIVO_CONECTORES', 2.0);
        }

        $precioPremarcoTapajuntas = $this->precioPremarcoYTapajuntas($abertura);

        // El original redondea cada bloque y vuelve a redondear la suma.
        $desglose = [
            'mh' => (int) round($precioMH),
            'mosquitero' => (int) round($precioMosq),
            'vidrio' => (int) round($precioVidrio),
            'conectores' => (int) round($precioConectores),
            'premarcoTapajuntas' => (int) round($precioPremarcoTapajuntas),
        ];

        $precio = (int) round(array_sum($desglose));

        $kgTotales = $kgMH + $kgMosq;
        $precioAluminio = $kgTotales * $this->coeficientes->coeficiente('COEF_DESPERDICIO', 1.0) * $precioKilo;

        $indices = [
            (int) round($precioAluminio / max($kgTotales, 0.000001) * 100),
            (int) round(($precioMH + $precioMosq) / max($precioAluminio + $accMH + $accMosq, 0.000001) * 100),
            (int) round($margenPorM2 / max($m2Vidrio, 0.000001) * 100),
            (int) round(($accMH + $accMosq) / max($kgTotales, 0.000001) * 100),
            100,
        ];

        return new ResultadoPrecio($precio, $desglose, $indices, $kgTotales, $m2Vidrio);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function kilosPorParte(AberturaParaPrecio $abertura): array
    {
        $kgMH = 0.0;
        $kgMosq = 0.0;

        foreach ($abertura->barras as $barra) {
            $kg = $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];

            match ($barra['parte']) {
                'MARCO', 'HOJA' => $kgMH += $kg,
                'MOSQUITERO' => $kgMosq += $kg,
                default => null,
            };
        }

        return [$kgMH, $kgMosq];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function accesoriosPorParte(AberturaParaPrecio $abertura): array
    {
        $accMH = 0.0;
        $accMosq = 0.0;

        foreach ($abertura->accesorios as $accesorio) {
            $total = $accesorio['precioUnitario'] * $accesorio['cantidad'];

            match ($accesorio['parte']) {
                'MARCO', 'HOJA' => $accMH += $total,
                'MOSQUITERO' => $accMosq += $total,
                default => null,
            };
        }

        return [$accMH, $accMosq];
    }

    private function precioPremarcoYTapajuntas(AberturaParaPrecio $abertura): float
    {
        // coefAjuste local fijo = 1.0, igual que el original.
        $total = 0.0;

        if ($abertura->premarco !== null) {
            $precioMetro = $this->coeficientes->precioMetroPremarco($abertura->premarco['codigo']);
            if ($precioMetro === null) {
                throw new RuntimeException("No hay precio de premarco para '{$abertura->premarco['codigo']}'.");
            }
            $total += $abertura->premarco['perimetroMM'] / 1000.0 * $precioMetro;
        }

        if ($abertura->tapajuntas !== null) {
            $precioMetro = $this->coeficientes->precioMetroTapajuntas($abertura->tapajuntas['codigo']);
            if ($precioMetro === null) {
                throw new RuntimeException("No hay precio de tapajuntas para '{$abertura->tapajuntas['codigo']}'.");
            }
            $total += $abertura->tapajuntas['perimetroMM'] / 1000.0 * $precioMetro;
        }

        return $total;
    }

    /**
     * Precio final aplicando moneda, IVA y ajuste de obra, en ese orden.
     */
    public function conMonedaImpuestosYAjuste(
        int $precioBase,
        bool $enPesos = false,
        float $cotizacion = 1.0,
        bool $agregarIva = false,
        float $coefAjuste = 1.0,
    ): int {
        $precio = (float) $precioBase;

        if ($enPesos) {
            $precio *= $cotizacion;
        }

        if ($agregarIva) {
            $precio *= 1.22;
        }

        return (int) round($precio * $coefAjuste);
    }
}
