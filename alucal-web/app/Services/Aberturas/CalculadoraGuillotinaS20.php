<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Guillotina de la línea Serie 20 (dos hojas que suben y bajan).
 *
 * Port de AberturaServiceGuillotina.getGuillotinaS20
 * (AberturaServiceGuillotina.java:286-368).
 *
 * No incluye: mosquitero, uniblock mix, premarco/tapajuntas ni grampas.
 * El cierre camarón sólo existe en negro y gris, igual que en el original.
 */
class CalculadoraGuillotinaS20
{
    public const TIPOLOGIA = 4;

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, punzonadoManual: bool,
     *     colorHerrajes: string, vidrio: array<string, mixed>|null
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];
        $anchoM = $ancho / 1000.0;
        $altoM = $alto / 1000.0;

        $manual = (bool) ($datos['punzonadoManual'] ?? false);
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'GRIS'));

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco.
        $lineas->agregarBarra('204', $peso('204'), 2 * $cantidad, $alto - 22, 'MARCO');
        $lineas->agregarBarra('205', $peso('205'), 2 * $cantidad, $ancho, 'MARCO');

        // Hojas: laterales (201) y horizontales (202), más el enganche central.
        $largo201 = $manual ? $alto / 2 - 74 : $alto / 2 - 9;
        $lineas->agregarBarra('201', $peso('201'), 4 * $cantidad, $largo201, 'HOJA');
        $lineas->agregarBarra('202', $peso('202'), 2 * $cantidad, $ancho - 48, 'HOJA');
        $lineas->agregarBarra('216', $peso('216'), 2 * $cantidad, $ancho - 48, 'HOJA');

        // Accesorios.
        $this->accesorio($lineas, '230478', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230447', 3.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, $color === 'NEGRO' ? '230066' : '230067', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '100203', 4.0 * $cantidad, 'HOJA');

        if ($manual) {
            $this->accesorio($lineas, '230460', 8.0 * $cantidad, 'HOJA');
        }

        $this->accesorio($lineas, '230941', (6.0 * $anchoM + 4.0 * $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '100104', (4.0 * $anchoM + 2.0 * $altoM) * $cantidad, 'HOJA');

        // Dos vidrios, uno por hoja.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 2 * $cantidad,
                anchoM: round($anchoM - 0.112, 3),
                altoM: round($altoM / 2.0 - 0.057, 3),
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
