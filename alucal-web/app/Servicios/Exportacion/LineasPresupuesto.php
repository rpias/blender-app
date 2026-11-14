<?php

namespace App\Servicios\Exportacion;

use App\Models\Obra;
use App\Services\Aberturas\CalcularVano;

/**
 * Arma las líneas del presupuesto: aberturas sueltas y vanos compuestos
 * con sus hijos (spec 0009).
 */
class LineasPresupuesto
{
    /**
     * @return list<array{
     *     tipo: string, numero: int, descripcion: string, medidas: string,
     *     cantidad: int, ubicacion: string, terminacion: string,
     *     unitario: float|null, subtotal: float,
     *     hijos: list<array{numero: int, descripcion: string, medidas: string, subtotal: float}>
     * }>
     */
    public function para(Obra $obra): array
    {
        $calcular = app(CalcularVano::class);
        $lineas = [];

        foreach ($obra->elementos as $elemento) {
            if ($elemento->tipoElemento === 'ABERTURA_COMPUESTA') {
                $hijos = $elemento->hijos()->with('aberturaSimple.tipologia')->get();

                if ($hijos->isEmpty()) {
                    continue;
                }

                $detalle = $calcular->detalle($elemento);
                $division = $elemento->vanoDivisiones()->first();

                $lineasHijas = [];
                foreach ($hijos as $hijo) {
                    $abertura = $hijo->aberturaSimple;
                    $lineasHijas[] = [
                        'numero' => (int) $hijo->numero,
                        'descripcion' => trim(($abertura?->tipologia?->nombre ?? 'Abertura').' '.($abertura?->serie?->nombre ?? '')),
                        'medidas' => $abertura ? number_format((float) $abertura->ancho, 0, ',', '.').' × '.number_format((float) $abertura->alto, 0, ',', '.') : '—',
                        'subtotal' => (float) ($abertura?->precioUnitario ?? 0) * (int) ($abertura?->cantidad ?? 0),
                    ];
                }

                $lineas[] = [
                    'tipo' => 'vano',
                    'numero' => (int) $elemento->numero,
                    'descripcion' => 'Vano compuesto ('.$hijos->count().' aberturas)',
                    'medidas' => $division ? 'conector '.$division->codigoConector : '—',
                    'cantidad' => 1,
                    'ubicacion' => (string) ($hijos->first()?->aberturaSimple?->ubicacion ?? '—') ?: '—',
                    'terminacion' => (string) ($division?->terminacion ?? '—'),
                    'unitario' => null,
                    'subtotal' => $detalle['total'],
                    'hijos' => $lineasHijas,
                ];

                continue;
            }

            $abertura = $elemento->aberturaSimple;

            if ($abertura === null) {
                continue;
            }

            $lineas[] = [
                'tipo' => 'abertura',
                'numero' => (int) $elemento->numero,
                'descripcion' => trim(($abertura->tipologia?->nombre ?? '').' / '.($abertura->serie?->nombre ?? ''), ' /'),
                'medidas' => number_format((float) $abertura->ancho, 0, ',', '.').' × '.number_format((float) $abertura->alto, 0, ',', '.'),
                'cantidad' => (int) $abertura->cantidad,
                'ubicacion' => (string) ($abertura->ubicacion ?: '—'),
                'terminacion' => str_replace('_', ' ', (string) $abertura->terminacion),
                'unitario' => $abertura->precioUnitario !== null ? (float) $abertura->precioUnitario : null,
                'subtotal' => (float) ($abertura->precioUnitario ?? 0) * (int) $abertura->cantidad,
                'hijos' => [],
            ];
        }

        return $lineas;
    }

    /**
     * @param  list<array<string, mixed>>  $lineas
     */
    public function total(array $lineas): float
    {
        return array_sum(array_column($lineas, 'subtotal'));
    }
}
