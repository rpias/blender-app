<?php

namespace App\Services\Precios;

/**
 * Resultado del cálculo: precio unitario entero y desglose auditable.
 *
 * El motor Java devuelve int[6]: precio + 5 indicadores (U$S/kg, margen medio,
 * margen de vidrio, accesorios por kg, coeficiente de ajuste ×100).
 */
class ResultadoPrecio
{
    /**
     * @param  array{mh: int, mosquitero: int, vidrio: int, conectores: int, premarcoTapajuntas: int}  $desglose
     * @param  list<int>  $indices
     */
    public function __construct(
        public int $precio,
        public array $desglose,
        public array $indices = [],
        public float $kgTotales = 0.0,
        public float $m2Vidrio = 0.0,
    ) {}
}
