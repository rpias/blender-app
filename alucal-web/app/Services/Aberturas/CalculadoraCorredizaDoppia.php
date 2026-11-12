<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Corrediza 2+1 de la línea Doppia (2 hojas móviles + 1 paño fijo).
 *
 * Port de AberturaServiceCorrediza.getCorrediza_2_mas_1_Doppia
 * (AberturaServiceCorrediza.java:12857-13002).
 *
 * No incluye: uniblock mix, premarco/tapajuntas, grampas, umbral embutido
 * ni el acristalamiento diferenciado del paño fijo.
 */
class CalculadoraCorredizaDoppia
{
    public const TIPOLOGIA = 14;

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, marcoInferior: string,
     *     marcoSuperior: string, paranteLateral: string, zocalo: string, cabezal: string,
     *     enganche: string, enganchePerfil?: string, colorHerrajes: string,
     *     vidrio: array<string, mixed>|null
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];
        $anchoM = $ancho / 1000.0;
        $altoM = $alto / 1000.0;

        $marcoInferior = (string) ($datos['marcoInferior'] ?? 'ADR 2494');
        $marcoSuperior = (string) ($datos['marcoSuperior'] ?? 'ADR 2494');
        $parante = (string) ($datos['paranteLateral'] ?? 'ADR 2474');
        $zocalo = (string) ($datos['zocalo'] ?? 'ADR 2204');
        $cabezal = (string) ($datos['cabezal'] ?? 'ADR 2204');
        $enganche = strtoupper((string) ($datos['enganche'] ?? 'SIMPLE'));
        $enganchePerfil = (string) ($datos['enganchePerfil'] ?? 'ADR 2479');
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'BLANCO'));

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco.
        $lineas->agregarBarra($marcoInferior, $peso($marcoInferior), 1 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra($marcoSuperior, $peso($marcoSuperior), 1 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra('ADR 2472', $peso('ADR 2472'), 1 * $cantidad, $alto, 'MARCO');
        $lineas->agregarBarra('ADR 2471', $peso('ADR 2471'), 1 * $cantidad, $alto, 'MARCO');
        $lineas->agregarBarra('ADR 2477', $peso('ADR 2477'), 1 * $cantidad, $ancho - 48, 'MARCO');

        $refuerzo = $marcoInferior === 'ADR 2496' ? 'ADR 1152' : 'ADR 1661';
        $lineas->agregarBarra($refuerzo, $peso($refuerzo), 2 * $cantidad, $ancho - 48, 'MARCO');

        $descuentoAltoHoja = 70;
        if ($marcoInferior === 'ADR 2496') {
            $descuentoAltoHoja += 45;
        }
        if ($marcoSuperior === 'ADR 2496') {
            $descuentoAltoHoja += 45;
        }

        $altoHoja = $alto - $descuentoAltoHoja;

        // Hoja móvil.
        $lineas->agregarBarra($parante, $peso($parante), 2 * $cantidad, $altoHoja, 'HOJA');
        $lineas->agregarBarra($zocalo, $peso($zocalo), 2 * $cantidad, $ancho / 2 - 16, 'HOJA');
        $lineas->agregarBarra($cabezal, $peso($cabezal), 2 * $cantidad, $ancho / 2 - 16, 'HOJA');

        if ($enganche === 'SIMPLE') {
            $lineas->agregarBarra('ADR 2473', $peso('ADR 2473'), 2 * $cantidad, $altoHoja, 'HOJA');
        } elseif ($enganche === 'REFORZADO') {
            $lineas->agregarBarra('ADR 2473', $peso('ADR 2473'), 1 * $cantidad, $altoHoja, 'HOJA');
            $lineas->agregarBarra($enganchePerfil, $peso($enganchePerfil), 1 * $cantidad, $altoHoja, 'HOJA');
        } elseif ($enganche === 'DOBLEMENTE REFORZADO') {
            $lineas->agregarBarra($enganchePerfil, $peso($enganchePerfil), 2 * $cantidad, $altoHoja, 'HOJA');
        } else {
            throw new SerieSinConstantes("Enganche '{$enganche}' inválido para Doppia 2+1.");
        }

        // Paño fijo.
        $lineas->agregarBarra('ADR 2476', $peso('ADR 2476'), 1 * $cantidad, $altoHoja, 'HOJA');
        $lineas->agregarBarra('ADR 2475', $peso('ADR 2475'), 1 * $cantidad, $altoHoja, 'HOJA');
        $lineas->agregarBarra('ADR 2475', $peso('ADR 2475'), 2 * $cantidad, $ancho / 2 - 28, 'HOJA');
        $lineas->agregarBarra('ADR 910', $peso('ADR 910'), 2 * $cantidad, $ancho / 2 - 45, 'HOJA');
        $lineas->agregarBarra('ADR 910', $peso('ADR 910'), 2 * $cantidad, $altoHoja - 55, 'HOJA');

        // Accesorios.
        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230087', 3.0 * $cantidad, 'MARCO');

        $juntasBajas = 0;
        $juntasAltas = 0;
        $marcoInferior === 'ADR 2496' ? $juntasAltas += 2 : $juntasBajas += 2;
        $marcoSuperior === 'ADR 2496' ? $juntasAltas += 2 : $juntasBajas += 2;

        if ($juntasBajas > 0) {
            $this->accesorio($lineas, '230313', $juntasBajas * $cantidad, 'MARCO');
        }
        if ($juntasAltas > 0) {
            $this->accesorio($lineas, '230314', $juntasAltas * $cantidad, 'MARCO');
        }

        $this->accesorio($lineas, 'AA806', 8.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230347', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA805', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, $color === 'NEGRO' ? '230030' : '230558', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230586', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230581', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, $color === 'NEGRO' ? '230470' : '230471', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230317', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230637', (4.0 * $altoM + 2.0 * $anchoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230632', (2.0 * $altoM + $anchoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230625', (2.0 * $altoM + $anchoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230827', 4.0 * $altoM * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230828', (4.0 * $altoM + 4.0 * $anchoM) * $cantidad, 'HOJA');

        // Vidrios: paño fijo y dos hojas móviles.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 1 * $cantidad,
                anchoM: round(($ancho / 2 - 45 - 10) / 1000.0, 3),
                altoM: round($altoM - $descuentoAltoHoja / 1000.0 - 0.027, 3),
            );

            $descuentoAltoVidrio = 0.095;
            if ($zocalo === 'ADR 2209') {
                $descuentoAltoVidrio += 0.055;
            }
            if ($cabezal === 'ADR 2209') {
                $descuentoAltoVidrio += 0.055;
            }

            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 2 * $cantidad,
                anchoM: round($anchoM / 2.0 - 0.1, 3),
                altoM: round($altoM - $descuentoAltoHoja / 1000.0 - $descuentoAltoVidrio, 3),
            );
        }

        return $lineas;
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
}
