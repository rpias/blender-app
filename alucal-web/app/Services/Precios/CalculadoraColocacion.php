<?php

namespace App\Services\Precios;

/**
 * Precio de colocación de una abertura (spec 0016).
 *
 * Port de ServiceCalcularPrecio_MargenOperativo.getPrecioColocacion: precio base
 * del tipo de colocación más el perímetro en metros por el precio por metro.
 */
class CalculadoraColocacion
{
    public function __construct(private readonly Coeficientes $coeficientes) {}

    /**
     * @return array{tipo: string, base: int, porMetro: int, metros: float, total: float}|null
     */
    public function calcular(float $anchoMM, float $altoMM, ?string $tipo): ?array
    {
        $tipo = trim((string) $tipo);

        if ($tipo === '') {
            return null;
        }

        $precios = $this->coeficientes->colocacion($tipo);

        if ($precios === null) {
            return null;
        }

        [$base, $porMetro] = $precios;
        $metros = 2 * ($anchoMM + $altoMM) / 1000.0;

        return [
            'tipo' => $tipo,
            'base' => $base,
            'porMetro' => $porMetro,
            'metros' => round($metros, 3),
            'total' => (float) round($base + $metros * $porMetro),
        ];
    }
}
