<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Corrediza Domina de 2 hojas.
 *
 * Port de AberturaServiceCorrediza.getCorredizaDomina_2Hojas
 * (AberturaServiceCorrediza.java:79-243), rama CORR_2H_SM / CORR_2H_CM1.
 *
 * No incluye (todavía): uniblock mix, mosquitero, umbral embutido, premarco y
 * tapajuntas, cierres personalizados.
 */
class CalculadoraCorredizaDomina
{
    public const TIPOLOGIA = 3;

    public function __construct(
        private readonly Constantes $constantes,
        private readonly Mosquitero $mosquitero,
    ) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, idSerie: int, cantidad?: int,
     *     enganche?: string, ruedaCodigo?: string, divisorAlMedio?: bool,
     *     espesorVidrio?: int, vidrio?: array{costoM2: float, precioMinimo: float, margen: float}|null
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];
        $anchoM = $ancho / 1000.0;
        $altoM = $alto / 1000.0;

        $enganche = (string) ($datos['enganche'] ?? 'SIMPLE');
        $ruedaCodigo = (string) ($datos['ruedaCodigo'] ?? '193155');
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);
        $divisorAlMedio = (bool) ($datos['divisorAlMedio'] ?? false);
        $esDVH = $espesor >= 14;

        $lineas = new LineasAbertura;
        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);

        // Marco perimetral 45-45 a medida nominal.
        $lineas->agregarBarra('9500', $peso('9500'), 2 * $cantidad, $ancho, 'MARCO');
        $lineas->agregarBarra('9500', $peso('9500'), 2 * $cantidad, $alto, 'MARCO');

        $altoHoja = $alto - 64;
        $anchoZocalo = (int) round($ancho / 2.0 - 25.0);

        // Vidrio: un enganche por hoja ⇒ ancho de hoja - 79; alto - 111.
        $anchoVidrio = $anchoZocalo - 79;
        $altoVidrio = $altoHoja - 111;
        if ($esDVH) {
            $anchoVidrio -= 4;
            $altoVidrio -= 4;
        }

        // Hojas.
        $lineas->agregarBarra('9525', $peso('9525'), 2 * $cantidad, $altoHoja, 'HOJA');
        $lineas->agregarBarra('9520', $peso('9520'), 4 * $cantidad, $anchoZocalo, 'HOJA');

        match ($enganche) {
            'SIMPLE' => $lineas->agregarBarra('9530', $peso('9530'), 2 * $cantidad, $altoHoja, 'HOJA'),
            'REFORZADO' => (function () use ($lineas, $peso, $cantidad, $altoHoja) {
                $lineas->agregarBarra('9530', $peso('9530'), 1 * $cantidad, $altoHoja, 'HOJA');
                $lineas->agregarBarra('9540', $peso('9540'), 1 * $cantidad, $altoHoja, 'HOJA');
            })(),
            'DOBLEMENTE REFORZADO' => $lineas->agregarBarra('9540', $peso('9540'), 2 * $cantidad, $altoHoja, 'HOJA'),
            default => throw new SerieSinConstantes("Tipo de enganche '{$enganche}' inválido para Domina."),
        };

        // Suplementos para vidrio simple (espesor <= 10 mm).
        if ($espesor <= 10) {
            $altoSuplemento = $altoHoja - 96;
            $anchoSuplemento = $anchoZocalo - 63;
            $lineas->agregarBarra('9580', $peso('9580'), 4 * $cantidad, $altoSuplemento, 'HOJA');
            $lineas->agregarBarra('9580', $peso('9580'), 4 * $cantidad, $anchoSuplemento, 'HOJA');
        }

        // Accesorios.
        $this->accesorio($lineas, '230420', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230049', round($anchoM / 0.5) * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230087', round($anchoM / 0.5) * $cantidad, 'MARCO');
        $this->accesorio($lineas, $ruedaCodigo, 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '231001', 1.0 * $cantidad, 'HOJA');

        if ($espesor <= 4) {
            $this->accesorio($lineas, '230637', (2.0 * $anchoM + 4.0 * $altoM) * $cantidad, 'HOJA');
        } else {
            $this->accesorio($lineas, '230639', (2.0 * $anchoM + 4.0 * $altoM) * $cantidad, 'HOJA');
            $this->accesorio($lineas, '230632', (2.0 * $anchoM + 4.0 * $altoM) * $cantidad, 'HOJA');
        }

        $this->accesorio($lineas, '230829', (4.0 * ($anchoM + $altoM) + 4.0 * $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '191280', 2.0 * $cantidad, 'HOJA');

        // Vidrios: dos paños, o cuatro si va divisor al medio.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            if ($divisorAlMedio) {
                $lineas->agregarBarra('9527', $peso('9527'), 2 * $cantidad, $anchoZocalo, 'HOJA');
                $altoMedio = ($altoVidrio / 1000.0 - 0.045) / 2.0;
                $this->vidrios($lineas, 4 * $cantidad, $anchoVidrio / 1000.0, $altoMedio, $vidrio);
            } else {
                $this->vidrios($lineas, 2 * $cantidad, $anchoVidrio / 1000.0, $altoVidrio / 1000.0, $vidrio);
            }
        }

        // Mosquitero fijo opcional sobre el vano completo.
        if ((bool) ($datos['mosquitero'] ?? false)) {
            $this->mosquitero->agregar($lineas, Mosquitero::PERFIL_DOMINA, $ancho, $alto, $cantidad);
        }

        return $lineas;
    }

    /**
     * @param  array{costoM2: float, precioMinimo: float, margen: float}  $vidrio
     */
    private function vidrios(LineasAbertura $lineas, int $cantidad, float $anchoM, float $altoM, array $vidrio): void
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
}
