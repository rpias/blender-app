<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Corrediza 2+2 de la línea Doppia (4 hojas móviles en 2 guías).
 *
 * Port de AberturaServiceCorrediza.getCorrediza_2_mas_2_Doppia
 * (AberturaServiceCorrediza.java:13003-13150).
 *
 * No incluye: uniblock mix, premarco/tapajuntas, grampas ni umbral embutido.
 */
class CalculadoraCorredizaDoppia22
{
    public const TIPOLOGIA = 15;

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
        $parante = (string) ($datos['paranteLateral'] ?? 'ADR 2474');
        $zocalo = (string) ($datos['zocalo'] ?? 'ADR 2204');
        $enganche = strtoupper((string) ($datos['enganche'] ?? 'SIMPLE'));
        $enganchePerfil = (string) ($datos['enganchePerfil'] ?? 'ADR 2479');
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'BLANCO'));

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco.
        $lineas->agregarBarra('ADR 2477', $peso('ADR 2477'), 1 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra('ADR 2495', $peso('ADR 2495'), 1 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra($marcoInferior, $peso($marcoInferior), 1 * $cantidad, $ancho - 48, 'MARCO');

        $descuentoHojaVertical = $marcoInferior === 'ADR 2497' ? 115 : 70;
        $refuerzo = $marcoInferior === 'ADR 2497' ? 'ADR 1152' : 'ADR 1661';
        $lineas->agregarBarra($refuerzo, $peso($refuerzo), 2 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra('ADR 2478', $peso('ADR 2478'), 2 * $cantidad, $alto, 'MARCO');

        $altoHoja = $alto - $descuentoHojaVertical;

        // Parantes: simple (6) o mixto (2 simples + 4 reforzados).
        if ($parante === 'ADR 2474') {
            $lineas->agregarBarra('ADR 2474', $peso('ADR 2474'), 6 * $cantidad, $altoHoja, 'HOJA');
        } else {
            $lineas->agregarBarra('ADR 2474', $peso('ADR 2474'), 2 * $cantidad, $altoHoja, 'HOJA');
            $lineas->agregarBarra('ADR 2479', $peso('ADR 2479'), 4 * $cantidad, $altoHoja, 'HOJA');
        }

        if ($enganche === 'SIMPLE') {
            $lineas->agregarBarra('ADR 2473', $peso('ADR 2473'), 2 * $cantidad, $altoHoja, 'HOJA');
        } elseif ($enganche === 'REFORZADO') {
            $lineas->agregarBarra('ADR 2473', $peso('ADR 2473'), 1 * $cantidad, $altoHoja, 'HOJA');
            $lineas->agregarBarra($enganchePerfil, $peso($enganchePerfil), 1 * $cantidad, $altoHoja, 'HOJA');
        } elseif ($enganche === 'DOBLEMENTE REFORZADO') {
            $lineas->agregarBarra($enganchePerfil, $peso($enganchePerfil), 2 * $cantidad, $altoHoja, 'HOJA');
        } else {
            throw new SerieSinConstantes("Enganche '{$enganche}' inválido para Doppia 2+2.");
        }

        $lineas->agregarBarra('ADR 2480', $peso('ADR 2480'), 2 * $cantidad, $altoHoja, 'HOJA');

        // Zócalo y cabezal.
        if ($zocalo === 'ADR 2204') {
            $lineas->agregarBarra('ADR 2204', $peso('ADR 2204'), 8 * $cantidad, $ancho / 2 - 16, 'HOJA');
        } else {
            $lineas->agregarBarra('ADR 2204', $peso('ADR 2204'), 4 * $cantidad, $ancho / 2 - 16, 'HOJA');
            $lineas->agregarBarra('ADR 2209', $peso('ADR 2209'), 4 * $cantidad, $ancho / 2 - 16, 'HOJA');
        }

        // Accesorios.
        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230087', 3.0 * $cantidad, 'MARCO');

        if ($marcoInferior === 'ADR 2497') {
            $this->accesorio($lineas, '192510', 2.0 * $cantidad, 'MARCO');
            $this->accesorio($lineas, '192515', 2.0 * $cantidad, 'MARCO');
        } else {
            $this->accesorio($lineas, '192510', 4.0 * $cantidad, 'MARCO');
        }

        $this->accesorio($lineas, '230347', 8.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA805', 8.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '192535', 8.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA808', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, $color === 'NEGRO' ? '230470' : '230471', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230317', 8.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230637', ($anchoM / 2.0 - 0.016 + $altoM - $descuentoHojaVertical / 1000.0) * 2.0 * 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230828', (8.0 * $anchoM + 8.0 * $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230827', 6.0 * $altoM * $cantidad, 'HOJA');

        // Vidrio: 4 hojas móviles.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $descuentoAltoVidrio = $zocalo === 'ADR 2204' ? 0.095 : 0.15;

            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 4 * $cantidad,
                anchoM: round($anchoM / 2.0 - 0.1, 3),
                altoM: round($altoM - $descuentoHojaVertical / 1000.0 - $descuentoAltoVidrio, 3),
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
