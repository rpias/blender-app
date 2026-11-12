<?php

namespace App\Services\Precios;

/**
 * Entrada del cálculo de precio: una abertura con sus líneas ya resueltas.
 *
 * Las cantidades las produce el motor de tipologías (aún por portar); acá sólo
 * se valorizan. Se puede construir desde un snapshot persistido o desde el
 * cálculo en memoria.
 */
class AberturaParaPrecio
{
    /**
     * @param  list<array{pesoKgPorMetro: float, cantidad: int, largoMM: float, parte: string}>  $barras
     * @param  list<array{precioUnitario: float, cantidad: float, parte: string}>  $accesorios
     * @param  list<array{tipo: string, cantidad: int, anchoM: float, altoM: float, costoM2?: float, precioMinimo?: float, margen?: float, extM2?: float, intM2?: float, camaraPorMetro?: float}>  $vidrios
     * @param  array{codigo: string, perimetroMM: float}|null  $premarco
     * @param  array{codigo: string, perimetroMM: float}|null  $tapajuntas
     */
    public function __construct(
        public float $anchoMM,
        public float $altoMM,
        public int $idTipologia,
        public int $idSerie,
        public string $terminacion,
        public bool $rpt = false,
        public int $cantidad = 1,
        public array $barras = [],
        public array $accesorios = [],
        public array $vidrios = [],
        public ?array $premarco = null,
        public ?array $tapajuntas = null,
    ) {}
}
