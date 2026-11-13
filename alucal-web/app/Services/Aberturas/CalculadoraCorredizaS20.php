<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Corrediza Serie 20 de 2 hojas.
 *
 * Port de AberturaServiceCorrediza.getCorredizaS20_2Hojas
 * (AberturaServiceCorrediza.java:6676-6812) con los accesorios por defecto.
 *
 * Limitaciones conocidas y verificadas en Alucal_v5.5/_analysis/aberturas.md:
 *  - cierres personalizados y mosquitero todavía no se cargan;
 *  - el rodamiento se elige con la rama de la serie 19 y un peso de hoja
 *    estimado (vidrio + perfiles de hoja), no con getPesoHoja completo.
 */
class CalculadoraCorredizaS20
{
    public const TIPOLOGIA = 3;

    public function __construct(
        private readonly Constantes $constantes,
        private readonly Mosquitero $mosquitero,
    ) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, idSerie: int, cantidad?: int,
     *     marcoLateral: string, marcoInferior: string, cajaDeAgua: bool,
     *     zocalo: string, cabezal: string, enganche: string,
     *     punzonadoManual?: bool, cantEnganches?: int,
     *     divisorAlMedio?: bool, divisor?: string|null,
     *     vidrio?: array{costoM2: float, precioMinimo: float, margen: float}|null
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $idSerie = (int) $datos['idSerie'];
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));

        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];
        $anchoM = $ancho / 1000.0;
        $altoM = $alto / 1000.0;

        $marcoLateral = (string) $datos['marcoLateral'];
        $marcoInferior = (string) $datos['marcoInferior'];
        $cajaDeAgua = (bool) $datos['cajaDeAgua'];
        $zocalo = (string) $datos['zocalo'];
        $cabezal = (string) $datos['cabezal'];
        $enganche = (string) $datos['enganche'];

        // Marco.
        $base = 0;
        if ($marcoLateral === '205') {
            $anchoMarco = $ancho - 22;
            $base = 10;
            $altoMarco = $alto;
        } elseif ($marcoLateral === '235' || $marcoLateral === '291') {
            $anchoMarco = $ancho;
            $altoMarco = $cajaDeAgua ? $alto - 23 - 15 : $alto - 23;
        } else {
            throw new SerieSinConstantes("Marco lateral '{$marcoLateral}' inválido para Serie 20.");
        }

        if (! in_array($marcoInferior, ['204', '236'], true)) {
            throw new SerieSinConstantes("Marco inferior '{$marcoInferior}' inválido para Serie 20.");
        }

        // Hojas.
        $altoHoja = $cajaDeAgua ? $alto - 63 : $alto - 48;
        $altoVidrioM = $altoHoja / 1000.0 - 0.068;

        if ($zocalo === '2210' || $zocalo === '2211') {
            $altoVidrioM -= 0.016;
        } elseif ($zocalo !== '201') {
            throw new SerieSinConstantes("Zócalo '{$zocalo}' inválido para Serie 20.");
        }

        if ($cabezal === '2210' || $cabezal === '2211') {
            $altoVidrioM -= 0.016;
        }

        if ($enganche === '203') {
            $altoEnganche = $alto - 85;
            $anchoZocaloManual = ($ancho - 148 - 2 * $base) / 2;
            $anchoVidrioM = ((($ancho - 148 - 2 * $base) / 2) + 14) / 1000.0;
        } else {
            $altoEnganche = $alto - 48;
            $anchoZocaloManual = ($ancho - 126 - 2 * $base) / 2;
            $anchoVidrioM = ((($ancho - 128 - 2 * $base) / 2) + 14) / 1000.0;
        }

        $anchoZocalo = ($datos['punzonadoManual'] ?? false) ? $anchoZocaloManual : $anchoZocaloManual + 63;

        $lineas = new LineasAbertura;
        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);

        // Barras de marco.
        if ($cajaDeAgua) {
            $lineas->agregarBarra('217', $peso('217'), 1 * $cantidad, $anchoMarco, 'MARCO');
            $lineas->agregarBarra('218', $peso('218'), 1 * $cantidad, $anchoMarco, 'MARCO');
            $lineas->agregarBarra('204', $peso('204'), 1 * $cantidad, $anchoMarco, 'MARCO');
        } elseif ($marcoInferior === '204') {
            $lineas->agregarBarra('204', $peso('204'), 2 * $cantidad, $anchoMarco, 'MARCO');
        } else {
            $lineas->agregarBarra($marcoInferior, $peso($marcoInferior), 1 * $cantidad, $anchoMarco, 'MARCO');
            $lineas->agregarBarra('204', $peso('204'), 1 * $cantidad, $anchoMarco, 'MARCO');
        }

        $lineas->agregarBarra($marcoLateral, $peso($marcoLateral), 2 * $cantidad, $altoMarco, 'MARCO');

        // Barras de hoja: zócalo/cabezal, laterales y enganche central.
        if ($zocalo === $cabezal) {
            $lineas->agregarBarra($cabezal, $peso($cabezal), 4 * $cantidad, $anchoZocalo, 'HOJA');
        } else {
            $lineas->agregarBarra($zocalo, $peso($zocalo), 2 * $cantidad, $anchoZocalo, 'HOJA');
            $lineas->agregarBarra($cabezal, $peso($cabezal), 2 * $cantidad, $anchoZocalo, 'HOJA');
        }

        $lineas->agregarBarra('202', $peso('202'), 2 * $cantidad, $altoHoja, 'HOJA');
        $lineas->agregarBarra($enganche, $peso($enganche), 2 * $cantidad, $altoEnganche, 'HOJA');

        // Accesorios.
        $accesorio = fn (string $codigo, float $cant, string $parte) => $this->accesorio($lineas, $codigo, $cant, $parte);

        $accesorio('230478', 2.0 * $cantidad, 'MARCO');
        $accesorio('230447', round($anchoM / 0.3) * $cantidad, 'MARCO');

        if ($cajaDeAgua) {
            $accesorio('196140', round($anchoM / 0.3) * $cantidad, 'MARCO');
        }

        $accesorio('230453', 1.0 * $cantidad, 'HOJA');
        $accesorio('230468', 2.0 * $cantidad, 'HOJA');
        $accesorio('230464', ($datos['cantEnganches'] ?? 2) * 4 * $cantidad, 'HOJA');
        $accesorio('230941', ($altoM * 6.0 + $anchoM * 4.0) * $cantidad, 'HOJA');

        // Burlete de hoja: 2 × ancho + alto × 2 × hojas.
        $accesorio('100104', (2.0 * $anchoM + $altoM * 2.0 * 2) * $cantidad, 'HOJA');

        // Vidrio: dos paños, o cuatro si va divisor al medio.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            if (($datos['divisorAlMedio'] ?? false) && ! empty($datos['divisor'])) {
                $lineas->agregarBarra((string) $datos['divisor'], $peso((string) $datos['divisor']), 2 * $cantidad, $anchoZocalo, 'HOJA');
                $altoMedio = ($altoVidrioM - 16 / 1000.0) / 2.0;
                $this->agregarVidrios($lineas, 4 * $cantidad, $anchoVidrioM, $altoMedio, $vidrio);
            } else {
                $this->agregarVidrios($lineas, 2 * $cantidad, $anchoVidrioM, $altoVidrioM, $vidrio);
            }
        }

        // Rodamiento (rama serie 19 de getCodigoRodamiento).
        $pesoHoja = $this->pesoHojaEstimado($lineas, $vidrio, $cantidad);
        $codigoRodamiento = $this->codigoRodamiento($idSerie, $pesoHoja, $zocalo);
        $accesorio($codigoRodamiento, 4.0 * $cantidad, 'HOJA');

        // Mosquitero fijo opcional sobre el vano completo.
        if ((bool) ($datos['mosquitero'] ?? false)) {
            $this->mosquitero->agregar($lineas, Mosquitero::PERFIL_S20, $ancho, $alto, $cantidad);
        }

        return $lineas;
    }

    /**
     * @param  array{costoM2: float, precioMinimo: float, margen: float}  $vidrio
     */
    private function agregarVidrios(LineasAbertura $lineas, int $cantidad, float $anchoM, float $altoM, array $vidrio): void
    {
        $lineas->agregarVidrio(
            cantidad: $cantidad,
            anchoM: round($anchoM, 3),
            altoM: round($altoM, 3),
            costoM2: (float) $vidrio['costoM2'],
            precioMinimo: (float) $vidrio['precioMinimo'],
            margen: (float) $vidrio['margen'],
        );
    }

    private function accesorio(LineasAbertura $lineas, string $codigo, float $cantidad, string $parte): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $accesorio = Accesorio::query()->where('codigo', $codigo)->first();

        if ($accesorio === null) {
            throw new SerieSinConstantes("Falta el accesorio '{$codigo}' en el catálogo.");
        }

        $cantPrecio = (int) $accesorio->cantPrecio;
        $precioUnitario = $cantPrecio > 0
            ? (float) $accesorio->precio / $cantPrecio / 100
            : (float) $accesorio->precio;

        $lineas->accesorios[] = [
            'codigo' => $codigo,
            'precioUnitario' => $precioUnitario,
            'cantidad' => round($cantidad, 3),
            'parte' => $parte,
        ];
    }

    /**
     * Peso aproximado de una hoja (kg): perfiles de hoja + vidrio.
     */
    private function pesoHojaEstimado(LineasAbertura $lineas, ?array $vidrio, int $cantidad): float
    {
        $kgHojas = 0.0;
        foreach ($lineas->barras as $barra) {
            if ($barra['parte'] === 'HOJA') {
                $kgHojas += $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];
            }
        }

        $kgPorUnidad = $cantidad > 0 ? $kgHojas / $cantidad : 0.0;

        $kgVidrio = 0.0;
        if ($vidrio !== null) {
            foreach ($lineas->vidrios as $linea) {
                $kgVidrio += $linea['anchoM'] * $linea['altoM'] * 4.0 * 2.5;
            }
            $kgVidrio = $lineas->vidrios === [] ? 0.0 : $kgVidrio;
        }

        return (int) round($kgPorUnidad + $kgVidrio);
    }

    private function codigoRodamiento(int $idSerie, int $pesoHoja, string $zocalo): string
    {
        // Rama serie 19 (AberturaServiceCorrediza.java:13982-13989).
        if ($idSerie === 19) {
            $conCaja = in_array($zocalo, ['2210', '2211'], true);

            if ($conCaja) {
                return '230279';
            }

            return $pesoHoja < 20 ? '230237' : '230279';
        }

        throw new SerieSinConstantes("No hay tabla de rodamientos portada para la serie {$idSerie}.");
    }
}
