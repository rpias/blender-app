<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;
use App\Models\Perfil;
use Illuminate\Support\Facades\Log;

/**
 * Corrediza con paño fijo de la línea Nórdical (1 o 2 hojas corredizas).
 *
 * Port de AberturaServiceCorredizaPañoFijo.getCorrediza_Nordical
 * (AberturaServiceCorredizaPañoFijo.java:34-153).
 *
 * Con vidrio simple agrega los adaptadores ADR 4237; si ese perfil no está en
 * el catálogo se omiten y queda registrado en el log (el precio baja).
 */
class CalculadoraCorredizaNordical
{
    public const TIPOLOGIA = 23;

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, cantHojasCorredizas: int,
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
        $hojas = (int) ($datos['cantHojasCorredizas'] ?? 1);

        if (! in_array($hojas, [1, 2], true)) {
            throw new SerieSinConstantes("Nórdical: cantidad de hojas inválida ({$hojas}).");
        }

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco.
        $lineas->agregarBarra('ADR 4251', $peso('ADR 4251'), 1 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra('ADR 4252', $peso('ADR 4252'), 1 * $cantidad, $ancho - 48, 'MARCO');
        $lineas->agregarBarra('ADR 3913', $peso('ADR 3913'), $hojas * $cantidad, $alto, 'MARCO');

        if ($hojas === 1) {
            $lineas->agregarBarra('ADR 4253', $peso('ADR 4253'), 1 * $cantidad, $alto, 'MARCO');
        }

        // Hojas.
        $eje = $ancho / 2;
        $lineas->agregarBarra('ADR 3942', $peso('ADR 3942'), $hojas * $cantidad, $alto - 98, 'HOJA');
        $lineas->agregarBarra('ADR 3943', $peso('ADR 3943'), $hojas * $cantidad, $alto - 76, 'HOJA');
        $lineas->agregarBarra('ADR 3944', $peso('ADR 3944'), $hojas * $cantidad, $alto - 98, 'HOJA');

        $agregarAdaptadores = ($datos['vidrio']['tipo'] ?? 'simple') !== 'dvh';
        if ($agregarAdaptadores) {
            $pesoAdaptador = $this->pesoOpcional('ADR 4237');

            if ($pesoAdaptador === null) {
                Log::warning('Nórdical: falta el perfil ADR 4237, se omiten los adaptadores de hoja.');
            } else {
                $adaptadores = $hojas * 2;
                $lineas->agregarBarra('ADR 4237', $pesoAdaptador, $adaptadores * $cantidad, $alto - 193, 'HOJA');
                $lineas->agregarBarra('ADR 4237', $pesoAdaptador, $adaptadores * $cantidad, $ancho / 2 - 69, 'HOJA');
            }
        }

        if ($hojas === 1) {
            $lineas->agregarBarra('ADR 3945', $peso('ADR 3945'), 2 * $cantidad, $ancho - $eje - 25, 'HOJA');
            $lineas->agregarBarra('ADR 4254', $peso('ADR 4254'), 2 * $cantidad, $ancho - $eje - 44, 'HOJA');
        } else {
            $lineas->agregarBarra('ADR 3945', $peso('ADR 3945'), 4 * $cantidad, ($ancho - 78) / 4, 'HOJA');
            $lineas->agregarBarra('ADR 4254', $peso('ADR 4254'), 4 * $cantidad, $ancho / 4 - 38, 'HOJA');
        }

        // Accesorios.
        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230087', floor($ancho / 400) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230361', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA710', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230290', $hojas * 2 * $hojas * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230379', $hojas * 2 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230820', $hojas * 2 * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA708', $hojas * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA711', $hojas * 2 * $cantidad, 'HOJA');
        $this->accesorio($lineas, 'AA498', $hojas * $cantidad, 'HOJA');

        if ($hojas === 1) {
            $this->accesorio($lineas, 'AA213', 20.0 * $cantidad, 'HOJA');
            $this->accesorio($lineas, 'AA709', 1.0 * $cantidad, 'HOJA');
        } else {
            $this->accesorio($lineas, 'AA213', 28.0 * $cantidad, 'HOJA');
        }

        // Vidrios: hojas corredizas y paño fijo.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: $hojas * $cantidad,
                anchoM: round(($ancho / 2 - 25) / 1000.0 - 0.053, 3),
                altoM: round($altoM - 0.23, 3),
            );

            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 1 * $cantidad,
                anchoM: round($anchoM / 2.0 - ($hojas === 1 ? 0.019 : 0.043), 3),
                altoM: round($altoM - 0.052, 3),
            );
        }

        return $lineas;
    }

    private function pesoOpcional(string $codigo): ?float
    {
        $perfil = Perfil::query()->where('Numero', $codigo)->first();

        return $perfil === null ? null : (float) $perfil->Peso;
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
