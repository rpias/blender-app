<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Corrediza HA 62 de 2 hojas.
 *
 * Port de AberturaServiceCorrediza.getCorredizaHA62_2Hojas
 * (AberturaServiceCorrediza.java, rama CORR_2H_SM / CORR_2H_CM1).
 *
 * Cubre marco a 45° (ADR 2464) y a 90° con 2 guías (ADR 1767/1769).
 * No incluye uniblock mix, mosquitero, umbral embutido ni premarco/tapajuntas.
 */
class CalculadoraCorredizaHA62
{
    public const TIPOLOGIA = 3;

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, idSerie: int, cantidad?: int,
     *     marco?: string, enganche?: string, espesorVidrio?: int,
     *     vidrio?: array{costoM2: float, precioMinimo: float, margen: float}|null
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];
        $anchoM = $ancho / 1000.0;
        $altoM = $alto / 1000.0;

        $marco = strtoupper(trim((string) ($datos['marco'] ?? '45 GRADOS')));
        $enganche = strtoupper(trim((string) ($datos['enganche'] ?? 'SIMPLE')));
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);
        $esNoventa = str_starts_with($marco, '90 GRADOS');

        $lineas = new LineasAbertura;
        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);

        if ($esNoventa) {
            $codigoH = 'ADR 1767';
            $codigoV = 'ADR 1769';
            $anchoUmbral = $ancho - 48;
            $altoHoja = $alto - 79;
            $anchoZocalo = $ancho / 2 + 5;
        } else {
            $codigoH = 'ADR 2464';
            $codigoV = 'ADR 2464';
            $anchoUmbral = $ancho;
            $altoHoja = $alto - 76;
            $anchoZocalo = $ancho / 2 - 2;
        }

        $altoMarco = $alto;

        // Marco: umbral/dintel y jambas.
        $lineas->agregarBarra($codigoH, $peso($codigoH), 2 * $cantidad, $anchoUmbral, 'MARCO');
        $lineas->agregarBarra($codigoV, $peso($codigoV), 2 * $cantidad, $altoMarco, 'MARCO');

        // Hojas: 4 horizontales y 4 verticales ADR 1770.
        $lineas->agregarBarra('ADR 1770', $peso('ADR 1770'), 4 * $cantidad, $anchoZocalo, 'HOJA');
        $lineas->agregarBarra('ADR 1770', $peso('ADR 1770'), 4 * $cantidad, $altoHoja, 'HOJA');

        // Cruce de hojas según parante.
        match ($enganche) {
            'SIMPLE' => $lineas->agregarBarra('ADR 2465', $peso('ADR 2465'), 2 * $cantidad, $altoHoja, 'HOJA'),
            'REFORZADO' => (function () use ($lineas, $peso, $cantidad, $altoHoja) {
                $lineas->agregarBarra('ADR 2465', $peso('ADR 2465'), 1 * $cantidad, $altoHoja, 'HOJA');
                $lineas->agregarBarra('ADR 1771', $peso('ADR 1771'), 1 * $cantidad, $altoHoja, 'HOJA');
            })(),
            'DOBLEMENTE REFORZADO' => $lineas->agregarBarra('ADR 1771', $peso('ADR 1771'), 2 * $cantidad, $altoHoja, 'HOJA'),
            default => throw new SerieSinConstantes("Tipo de parante '{$enganche}' inválido para HA 62."),
        };

        // Suplementos para vidrio simple.
        if ($espesor < 14) {
            $lineas->agregarBarra('ADR 1774', $peso('ADR 1774'), 4 * $cantidad, $anchoZocalo - 107, 'HOJA');
            $lineas->agregarBarra('ADR 1774', $peso('ADR 1774'), 4 * $cantidad, $altoHoja - 107, 'HOJA');
        }

        // Accesorios fijos.
        $this->accesorio($lineas, '230045', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230087', 2.0 * $cantidad, 'MARCO');

        if ($esNoventa) {
            $this->accesorio($lineas, '193580', 4.0 * $cantidad, 'MARCO');
            $this->accesorio($lineas, '193600', 4.0 * $cantidad, 'MARCO');
            $this->accesorio($lineas, '230361', 4.0 * $cantidad, 'MARCO');
        } else {
            $this->accesorio($lineas, '230419', 4.0 * $cantidad, 'MARCO');
            $this->accesorio($lineas, '230374', 2.0 * $cantidad, 'MARCO');
        }

        $this->accesorio($lineas, '230158', 2.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230340', 16.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230410', 8.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230372', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230360', 8.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230828', (4.0 * $anchoM + 6.0 * $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230640', (4.0 * $anchoM + 8.0 * $altoM) * $cantidad, 'HOJA');

        // Vidrio: (zócalo - 123) × (altoHoja - 123) mm por hoja.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrio(
                cantidad: 2 * $cantidad,
                anchoM: round(($anchoZocalo - 123) / 1000.0, 3),
                altoM: round(($altoHoja - 123) / 1000.0, 3),
                costoM2: (float) $vidrio['costoM2'],
                precioMinimo: (float) $vidrio['precioMinimo'],
                margen: (float) $vidrio['margen'],
            );
        }

        // Rodamiento según peso de hoja (rama serie 10).
        $pesoHoja = $this->pesoHoja($lineas, $cantidad);
        $this->accesorio($lineas, $this->codigoRodamiento($pesoHoja), 4.0 * $cantidad, 'HOJA');

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

    private function pesoHoja(LineasAbertura $lineas, int $cantidad): int
    {
        $kgHojas = 0.0;
        foreach ($lineas->barras as $barra) {
            if ($barra['parte'] === 'HOJA') {
                $kgHojas += $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];
            }
        }

        $kgVidrio = 0.0;
        foreach ($lineas->vidrios as $vidrio) {
            $kgVidrio += $vidrio['anchoM'] * $vidrio['altoM'] * 4.0 * 2.5 * $vidrio['cantidad'];
        }

        return (int) round(($cantidad > 0 ? $kgHojas / $cantidad : 0.0) + $kgVidrio);
    }

    private function codigoRodamiento(int $pesoHoja): string
    {
        // Rama serie 10 (AberturaServiceCorrediza.java, getCodigoRodamiento).
        return match (true) {
            $pesoHoja < 40 => '230233',
            $pesoHoja < 100 => '231007',
            $pesoHoja < 120 => '230574',
            default => '230236',
        };
    }
}
