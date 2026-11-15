<?php

namespace App\Services\Aberturas;

/**
 * Cortes de premarco y tapajuntas (spec 0017).
 *
 * Porta las tablas de AberturaService.agregarPremarco (línea 2552) y
 * agregarTapajuntas (tabla de descuentos). Se usa sólo para el cómputo de
 * materiales: el precio se sigue cobrando por metro lineal en el motor.
 */
class CortesPerimetrales
{
    /**
     * Perfil de premarco: [mm que se suman al corte, escuadra, escuadras por esquina].
     *
     * @var array<string, array{0: int, 1: string, 2: int}>
     */
    private const PREMARCO = [
        '1485' => [26, '190850', 2],
        '18252' => [0, '196780', 1],
        '2195' => [35, '', 0],
        '4570' => [32, '230736', 1],
        '4571' => [32, '190850', 2],
        '4577' => [32, '190849', 2],
        '4579' => [32, '190853', 2],
        '4587' => [30, '196780', 1],
        '4588' => [51, '230149', 1],
        '6066' => [30, '190832', 1],
        '6079' => [30, 'E-79', 1],
        '6205' => [30, '230666', 1],
        '6901' => [30, '230667', 1],
        '7227' => [30, '230666', 1],
        'ADR 230' => [43, '230149', 1],
        'ADR 2461' => [45, '230149', 1],
        'ADR 2548' => [45, '230149', 1],
        'ADR 761' => [43, '230149', 1],
    ];

    /** Descuento al corte del tapajuntas, por código. */
    private const TAPAJUNTAS = [
        'ADR 62' => 66,
        'ADR 191' => 87,
        'ADR 561' => 87,
        'ADR 1164' => 34,
        'ADR 1297' => 84,
        'ADR 1298' => 86,
        'ADR 1517' => 50,
        'ADR 1662' => 67,
        'ADR 2462' => -3,
        '4574' => 6,
        '6206' => 53,
        '7211' => 58,
        '16002' => 45,
        '18232' => 10,
        '18662' => 68,
    ];

    /**
     * Cortes de premarco para una abertura.
     *
     * @return array{barras: list<array{codigo: string, largoMM: float, cantidad: int}>, escuadras: array{codigo: string, cantidad: int}|null}
     */
    public function premarco(string $codigo, float $ancho, float $alto, bool $conAbajo): array
    {
        [$suma, $escuadra, $porEsquina] = self::PREMARCO[$codigo] ?? [0, '196780', 1];

        if ($conAbajo) {
            $barras = [
                ['codigo' => $codigo, 'largoMM' => $ancho + $suma, 'cantidad' => 2],
                ['codigo' => $codigo, 'largoMM' => $alto + $suma, 'cantidad' => 2],
            ];
            $cantidadEscuadras = $porEsquina * 4;
        } else {
            $barras = [
                ['codigo' => $codigo, 'largoMM' => $ancho + $suma, 'cantidad' => 1],
                ['codigo' => $codigo, 'largoMM' => $alto + $suma / 2, 'cantidad' => 2],
            ];
            $cantidadEscuadras = $porEsquina * 2;
        }

        return [
            'barras' => $barras,
            'escuadras' => $escuadra !== '' && $cantidadEscuadras > 0
                ? ['codigo' => $escuadra, 'cantidad' => $cantidadEscuadras]
                : null,
        ];
    }

    /**
     * Cortes de tapajuntas para una abertura.
     *
     * @return array{barras: list<array{codigo: string, largoMM: float, cantidad: int}>}
     */
    public function tapajuntas(string $codigo, float $ancho, float $alto, bool $conAbajo): array
    {
        $descuento = self::TAPAJUNTAS[$codigo] ?? 0;

        if ($conAbajo) {
            return ['barras' => [
                ['codigo' => $codigo, 'largoMM' => $ancho + $descuento, 'cantidad' => 2],
                ['codigo' => $codigo, 'largoMM' => $alto + $descuento, 'cantidad' => 2],
            ]];
        }

        return ['barras' => [
            ['codigo' => $codigo, 'largoMM' => $ancho + $descuento, 'cantidad' => 1],
            ['codigo' => $codigo, 'largoMM' => $alto + $descuento / 2, 'cantidad' => 2],
        ]];
    }
}
