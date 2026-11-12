<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Batiente de 1 o 2 hojas (línea Prima / Segunda generación).
 *
 * Port de AberturaServiceBatiente.getBatientePrima_1Hoja / _2Hojas
 * (AberturaServiceBatiente.java:382-426 y 596-636), con los códigos de
 * bisagra y cierre resueltos por color de herrajes (AberturaService.java:3205).
 *
 * No incluye: mosquitero fijo, uniblock mix, premarco/tapajuntas,
 * grampas ni variantes de doble contacto.
 */
class CalculadoraBatiente
{
    public const TIPOLOGIA = 2;

    private const MARCO = '6210';

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, cantHojas: int,
     *     hoja: string, contravidrios: string, cierre: string, colorHerrajes: string,
     *     espesorVidrio: int, vidrio: array<string, mixed>|null
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];
        $anchoM = $ancho / 1000.0;
        $altoM = $alto / 1000.0;

        $cantHojas = (int) ($datos['cantHojas'] ?? 1);
        $hoja = strtoupper((string) ($datos['hoja'] ?? 'RECTA'));
        $contravidrios = strtoupper((string) ($datos['contravidrios'] ?? 'RECTOS'));
        $cierre = strtoupper((string) ($datos['cierre'] ?? 'FALLEBA'));
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'BLANCO'));
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);
        $tieneDvh = ($datos['vidrio']['tipo'] ?? 'simple') === 'dvh';

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);

        $lineas = new LineasAbertura;

        // Marco: 2 horizontales (umbral y dintel) + 2 verticales.
        $lineas->agregarBarra(self::MARCO, $peso(self::MARCO), 2 * $cantidad, $ancho, 'MARCO');
        $lineas->agregarBarra(self::MARCO, $peso(self::MARCO), 2 * $cantidad, $alto, 'MARCO');

        $perfilHoja = $hoja === 'CURVA' ? '6234' : '6211';
        $contravidrio = $this->contravidrio($hoja, $contravidrios, $tieneDvh);

        if ($cantHojas === 1) {
            $hojaHorizontal = $ancho - 39;
            $hojaVertical = $alto - 39;

            $lineas->agregarBarra($perfilHoja, $peso($perfilHoja), 2 * $cantidad, $hojaHorizontal, 'HOJA');
            $lineas->agregarBarra($perfilHoja, $peso($perfilHoja), 2 * $cantidad, $hojaVertical, 'HOJA');
        } else {
            $hojaHorizontal = $ancho / 2 - 23;
            $hojaVertical = $alto - 39;

            $lineas->agregarBarra('6223', $peso('6223'), 1 * $cantidad, $alto - 104, 'HOJA');
            $lineas->agregarBarra($perfilHoja, $peso($perfilHoja), 4 * $cantidad, $hojaHorizontal, 'HOJA');
            $lineas->agregarBarra($perfilHoja, $peso($perfilHoja), 4 * $cantidad, $hojaVertical, 'HOJA');
        }

        $cantContravidrios = $cantHojas === 1 ? 2 : 4;
        $lineas->agregarBarra($contravidrio, $peso($contravidrio), $cantContravidrios * $cantidad, $hojaHorizontal, 'HOJA');
        $lineas->agregarBarra($contravidrio, $peso($contravidrio), $cantContravidrios * $cantidad, $hojaVertical, 'HOJA');

        // Accesorios.
        $this->accesorio($lineas, '230420', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, $this->bisagra($color), ($cantHojas === 1 ? 2.0 : 4.0) * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230049', ($cantHojas === 1 ? round($ancho / 500.0) : floor($ancho / 500)) * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230630', ($anchoM + $altoM) * 2.0 * $cantidad, 'MARCO');

        $this->accesorio($lineas, '230420', ($cantHojas === 1 ? 4.0 : 8.0) * $cantidad, 'HOJA');
        $this->accesorio(
            $lineas,
            '230627',
            ($cantHojas === 1 ? ($anchoM + $altoM) * 2.0 : $anchoM * 2.0 + $altoM * 4.0) * $cantidad,
            'HOJA',
        );

        if ($cantHojas === 1) {
            if ($cierre === 'FALLEBA') {
                $this->accesorio($lineas, $this->codigoH48($color), 1.0 * $cantidad, 'HOJA');
                $this->accesorio($lineas, '230244', 1.0 * $cantidad, 'HOJA');
            } else {
                $this->accesorio($lineas, $this->codigoH91($color), 1.0 * $cantidad, 'HOJA');
            }
        } else {
            if ($cierre === 'FALLEBA') {
                $this->accesorio($lineas, $this->codigoH48($color), 1.0 * $cantidad, 'HOJA');
                $this->accesorio($lineas, '230244', 1.0 * $cantidad, 'HOJA');
                $this->accesorio($lineas, '230081', 2.0 * $cantidad, 'HOJA');
                $this->accesorio($lineas, '230549', 2.0 * $cantidad, 'HOJA');
            } else {
                $this->accesorio($lineas, $this->codigoH91($color), 1.0 * $cantidad, 'HOJA');
            }

            $this->accesorio($lineas, '230100', 1.0 * $cantidad, 'HOJA');
        }

        $this->burletes($lineas, $espesor, (int) $ancho, (int) $alto, $cantHojas, $cantidad);

        if ($contravidrios === 'CURVOS') {
            $this->accesorio($lineas, '230089', (int) (2.0 * ($anchoM + $altoM) / 0.15) * $cantidad, 'HOJA');
        }

        // Vidrio: (vertical − 92) × (horizontal − 92) por hoja.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: $cantHojas * $cantidad,
                anchoM: round(($hojaHorizontal - 92) / 1000.0, 3),
                altoM: round(($hojaVertical - 92) / 1000.0, 3),
            );
        }

        return $lineas;
    }

    private function contravidrio(string $hoja, string $contravidrios, bool $dvh): string
    {
        $curvos = $contravidrios === 'CURVOS';

        return match (true) {
            $hoja === 'RECTA' && $curvos => $dvh ? '6232' : '6257',
            $hoja === 'RECTA' => $dvh ? '6225' : '6212',
            $hoja === 'CURVA' && $curvos => $dvh ? '6231' : '6230',
            $hoja === 'CURVA' => $dvh ? '6226' : '6217',
            default => throw new SerieSinConstantes("Hoja '{$hoja}' inválida para batiente."),
        };
    }

    private function bisagra(string $color): string
    {
        return $color === 'NEGRO' ? '230177' : '230178';
    }

    private function codigoH48(string $color): string
    {
        return $color === 'NEGRO' ? '230184' : '230183';
    }

    private function codigoH91(string $color): string
    {
        return $color === 'NEGRO' ? '230306' : '230134';
    }

    private function burletes(LineasAbertura $lineas, int $espesor, int $ancho, int $alto, int $cantHojas, int $cantidad): void
    {
        $cantidadBurlete = ($ancho * 2 + $alto * 2 * $cantHojas) / 1000.0 * $cantidad;

        $this->accesorio($lineas, '230632', $cantidadBurlete, 'HOJA');

        $codigo = match (true) {
            $espesor <= 4 => '100169',
            $espesor === 5 => '230625',
            $espesor === 6 => '230624',
            $espesor <= 17 => '100113',
            default => '100169',
        };

        $this->accesorio($lineas, $codigo, $cantidadBurlete, 'HOJA');
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
