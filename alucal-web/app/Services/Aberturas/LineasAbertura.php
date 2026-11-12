<?php

namespace App\Services\Aberturas;

/**
 * Líneas físicas de una abertura calculada.
 *
 * Cada barra: pesoKgPorMetro, cantidad, largoMM, parte, perfil (código).
 * Cada accesorio: precioUnitario, cantidad, parte, codigo.
 * Cada vidrio: tipo, cantidad, anchoM, altoM y costos.
 */
class LineasAbertura
{
    /**
     * @param  list<array{perfil: string, pesoKgPorMetro: float, cantidad: int, largoMM: float, parte: string}>  $barras
     * @param  list<array{codigo: string, precioUnitario: float, cantidad: float, parte: string}>  $accesorios
     * @param  list<array{tipo: string, cantidad: int, anchoM: float, altoM: float, costoM2: float, precioMinimo: float, margen: float}>  $vidrios
     */
    public function __construct(
        public array $barras = [],
        public array $accesorios = [],
        public array $vidrios = [],
    ) {}

    public function agregarBarra(string $perfil, float $pesoKgPorMetro, int $cantidad, float $largoMM, string $parte = 'MARCO'): void
    {
        if ($cantidad <= 0 || $largoMM <= 0) {
            return;
        }

        $this->barras[] = [
            'perfil' => $perfil,
            'pesoKgPorMetro' => $pesoKgPorMetro,
            'cantidad' => $cantidad,
            'largoMM' => round($largoMM, 1),
            'parte' => $parte,
        ];
    }

    public function agregarVidrio(
        int $cantidad,
        float $anchoM,
        float $altoM,
        float $costoM2,
        float $precioMinimo,
        float $margen,
        string $tipo = 'simple',
    ): void {
        if ($cantidad <= 0 || $anchoM <= 0 || $altoM <= 0) {
            return;
        }

        $this->vidrios[] = [
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'anchoM' => $anchoM,
            'altoM' => $altoM,
            'costoM2' => $costoM2,
            'precioMinimo' => $precioMinimo,
            'margen' => $margen,
        ];
    }

    /**
     * Agrega un vidrio a partir de un payload ya resuelto (simple o DVH).
     *
     * @param  array<string, mixed>  $vidrio
     */
    public function agregarVidrioPayload(array $vidrio, int $cantidad, float $anchoM, float $altoM): void
    {
        if ($cantidad <= 0 || $anchoM <= 0 || $altoM <= 0) {
            return;
        }

        $this->vidrios[] = array_merge($vidrio, [
            'cantidad' => $cantidad,
            'anchoM' => $anchoM,
            'altoM' => $altoM,
        ]);
    }

    public function kilos(): float
    {
        $kg = 0.0;
        foreach ($this->barras as $barra) {
            $kg += $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];
        }

        return $kg;
    }
}
