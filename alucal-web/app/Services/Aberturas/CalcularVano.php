<?php

namespace App\Services\Aberturas;

use App\Models\ElementoObra;
use App\Models\Perfil;
use App\Services\Precios\Coeficientes;

/**
 * Precio de un vano compuesto (spec 0009).
 *
 * El total es la suma de las aberturas hijas más los conectores de cada
 * división, calculados por kilo con el precio de la terminación.
 */
class CalcularVano
{
    public function __construct(private readonly Coeficientes $coeficientes) {}

    /**
     * Detalle del vano: hijos, conectores y total.
     *
     * @return array{hijos: list<array{id: int, precio: float, cantidad: int}>, conectores: list<array{codigo: string, kilos: float, precio: float}>, total: float}
     */
    public function detalle(ElementoObra $vano): array
    {
        $hijos = [];
        $totalHijos = 0.0;

        foreach ($vano->hijos()->with('aberturaSimple')->get() as $hijo) {
            $precio = (float) ($hijo->aberturaSimple?->precioUnitario ?? 0);
            $cantidad = (int) ($hijo->aberturaSimple?->cantidad ?? 1);

            if ($precio > 0) {
                $hijos[] = ['id' => $hijo->id, 'precio' => $precio, 'cantidad' => $cantidad];
                $totalHijos += $precio * $cantidad;
            }
        }

        $conectores = [];
        $totalConectores = 0.0;

        foreach ($vano->vanoDivisiones()->get() as $division) {
            $perfil = Perfil::query()->where('Numero', $division->codigoConector)->first();

            if ($perfil === null) {
                throw new SerieSinConstantes("Falta el perfil conector '{$division->codigoConector}'.");
            }

            $metros = max(1, (int) $division->posicionMM) / 1000.0;
            $kg = $metros * (float) $perfil->Peso;
            $porKilo = $this->coeficientes->precioPorKilo((string) ($division->terminacion ?? 'PINTADO_BLANCO'), false);
            $precio = round($kg * $porKilo * 1.08 * 1.8);

            $conectores[] = [
                'codigo' => (string) $division->codigoConector,
                'kilos' => round($kg, 3),
                'precio' => (float) $precio,
            ];

            $totalConectores += $precio;
        }

        return [
            'hijos' => $hijos,
            'conectores' => $conectores,
            'total' => round($totalHijos + $totalConectores),
        ];
    }
}
