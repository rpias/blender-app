<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Corrediza Serie 25 de 2 hojas.
 *
 * Port de AberturaService.getCorredizaS25_2Hojas (AberturaServiceCorrediza.java:7366-7521)
 * con los helpers getMedidaZocaloCorredizaS25 / getMedidaParantesHojaCorredizaS25
 * y getAltoZocaloCabezalCorredizaS25 de AberturaService.java:1449-1512.
 *
 * No incluye: uniblock mix, mosquitero, umbral embutido, premarco/tapajuntas,
 * encuentro a tope ni cierres personalizados.
 */
class CalculadoraCorredizaS25
{
    public const TIPOLOGIA = 3;

    private const BASE_MARCO = [
        '2501' => 25.5, '2534' => 25.5, '2515' => 9.0, '2518' => 1.5, '4565' => 28.5,
    ];

    private const PIE_PARANTE = ['2544' => 13.8, '4505' => 13.8, '4506' => 13.8, '255' => 16.5, '266' => 16.5];

    private const ANCHO_ENGANCHE = ['4507' => 36.0, '4537' => 36.0, '4538' => 36.0, '257' => 58.0, '258' => 58.0, '269' => 58.0];

    private const ALTO_ZOCALO = ['4503' => 60, '4504' => 45, '4524' => 100];

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, idSerie: int, cantidad?: int,
     *     marcoLateral: string, cajaDeAgua: bool, paranteLateral: string,
     *     zocalo: string, cabezal: string, enganche: string, enganchePerfil?: string,
     *     punzonadoManual: bool, tipoRueda: string, divisorAlMedio: bool,
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

        $marcoLateral = (string) $datos['marcoLateral'];
        $parante = (string) $datos['paranteLateral'];
        $zocalo = (string) $datos['zocalo'];
        $cabezal = (string) $datos['cabezal'];
        $enganche = strtoupper((string) $datos['enganche']);
        $cajaDeAgua = (bool) $datos['cajaDeAgua'];
        $punzonadoManual = (bool) $datos['punzonadoManual'];
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);

        $lineas = new LineasAbertura;
        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);

        // Marco.
        $anchoMarcoHorizontal = match ($marcoLateral) {
            '2501' => $ancho - 50,
            '2515' => $ancho - 18,
            '2518' => $ancho,
            default => throw new SerieSinConstantes("Marco lateral '{$marcoLateral}' inválido para Serie 25."),
        };
        $altoMarcoVertical = $marcoLateral === '2518'
            ? ($cajaDeAgua ? $alto - 66 - 17 : $alto - 66)
            : $alto;

        $lineas->agregarBarra($marcoLateral, $peso($marcoLateral), 2 * $cantidad, $altoMarcoVertical, 'MARCO');
        $lineas->agregarBarra('2528', $peso('2528'), 1 * $cantidad, $anchoMarcoHorizontal, 'MARCO');

        if ($cajaDeAgua) {
            $lineas->agregarBarra('2536', $peso('2536'), 1 * $cantidad, $anchoMarcoHorizontal, 'MARCO');
            $lineas->agregarBarra('2539', $peso('2539'), 1 * $cantidad, $anchoMarcoHorizontal, 'MARCO');
        } else {
            $lineas->agregarBarra('2500', $peso('2500'), 1 * $cantidad, $anchoMarcoHorizontal, 'MARCO');
        }

        $engancheComun = strlen($parante) === 3 ? '257' : '4507';

        // Zócalo / cabezal de hoja.
        $anchoCabezalZocalo = $this->anchoZocalo($ancho, 2, 2, $marcoLateral, $engancheComun, $parante, $punzonadoManual);
        $lineas->agregarBarra($cabezal, $peso($cabezal), 2 * $cantidad, $anchoCabezalZocalo, 'HOJA');
        $lineas->agregarBarra($zocalo, $peso($zocalo), 2 * $cantidad, $anchoCabezalZocalo, 'HOJA');

        // Parantes laterales y enganche central.
        $altoVerticales = (int) round($cajaDeAgua ? $alto - 48 - 16.2 : $alto - 48);
        $lineas->agregarBarra($parante, $peso($parante), 2 * $cantidad, $altoVerticales, 'HOJA');

        $engancheOtro = (string) ($datos['enganchePerfil'] ?? '4507');
        match ($enganche) {
            'SIMPLE' => $lineas->agregarBarra($engancheComun, $peso($engancheComun), 2 * $cantidad, $altoVerticales, 'HOJA'),
            'REFORZADO' => (function () use ($lineas, $peso, $cantidad, $altoVerticales, $engancheComun, $engancheOtro) {
                $lineas->agregarBarra($engancheComun, $peso($engancheComun), 1 * $cantidad, $altoVerticales, 'HOJA');
                $lineas->agregarBarra($engancheOtro, $peso($engancheOtro), 1 * $cantidad, $altoVerticales, 'HOJA');
            })(),
            'DOBLEMENTE REFORZADO' => $lineas->agregarBarra($engancheOtro, $peso($engancheOtro), 2 * $cantidad, $altoVerticales, 'HOJA'),
            default => throw new SerieSinConstantes("Enganche '{$enganche}' inválido para Serie 25."),
        };

        if ((bool) ($datos['divisorAlMedio'] ?? false)) {
            $lineas->agregarBarra('2548', $peso('2548'), 2 * $cantidad, $anchoCabezalZocalo, 'HOJA');
        }

        // Suplementos DVH (espesor > 13).
        if ($espesor > 13) {
            $altoSuplemento = $altoVerticales - $this->altoZocalo($zocalo) - $this->altoZocalo($cabezal);
            $anchoSuplemento = $this->anchoZocalo($ancho, 2, 2, $marcoLateral, $engancheComun, $parante, true) - 2;
            $lineas->agregarBarra('4590', $peso('4590'), 4 * $cantidad, $altoSuplemento, 'HOJA');
        }

        // Accesorios.
        $this->accesorio($lineas, '230478', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230447', round($anchoM / 0.5) * $cantidad, 'MARCO');

        if ($cajaDeAgua) {
            $this->accesorio($lineas, '196140', round($anchoM / 0.5) * $cantidad, 'MARCO');
        }

        $this->accesorio($lineas, '230451', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230470', 2.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, $punzonadoManual ? '230461' : '230465', ($punzonadoManual ? 8.0 : 4.0) * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230941', ($altoM * 6.0 + $anchoM * 4.0) * $cantidad, 'HOJA');

        // Burlete según espesor del acristalamiento.
        $largoBurlete = (2.0 * $anchoM + 2 * 2.0 * $altoM) * $cantidad;
        $this->burlete($lineas, $espesor, $largoBurlete);

        // Vidrio (acristalamiento entero).
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $anchoVidrio = $this->anchoZocalo($ancho, 2, 2, $marcoLateral, $engancheComun, $parante, true) + 16;
            $altoVidrio = $altoVerticales - $this->altoZocalo($zocalo) - $this->altoZocalo($cabezal) + 18;

            if ($espesor >= 14) {
                $anchoVidrio -= 38;
                $altoVidrio -= 38;
            }

            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 2 * $cantidad,
                anchoM: round($anchoVidrio / 1000.0, 3),
                altoM: round($altoVidrio / 1000.0, 3),
            );
        }

        // Rodamiento (serie 20 / 30 según tipo de rueda).
        $pesoHoja = $this->pesoHoja($lineas, $cantidad);
        $this->accesorio($lineas, $this->codigoRodamiento($pesoHoja, $zocalo, (string) $datos['tipoRueda']), 4.0 * $cantidad, 'HOJA');

        return $lineas;
    }

    private function anchoZocalo(int $ancho, int $cantHojas, int $cantGuias, string $marcoLateral, string $enganche, string $parante, bool $punzonadoManual): int
    {
        $base = self::BASE_MARCO[$marcoLateral] ?? throw new SerieSinConstantes("Marco '{$marcoLateral}' inválido.");
        $pie = self::PIE_PARANTE[$parante] ?? throw new SerieSinConstantes("Parante '{$parante}' inválido.");
        $anchoEnganche = self::ANCHO_ENGANCHE[$enganche] ?? throw new SerieSinConstantes("Enganche '{$enganche}' inválido.");

        $resultado = ($ancho - 2 * $base - 6.4 - 2 * $pie + $anchoEnganche) / 2.0;

        if ($punzonadoManual) {
            $resultado -= strlen($parante) === 3 ? 114.0 : 72.0;
        }

        return (int) round($resultado);
    }

    private function altoZocalo(string $codigo): int
    {
        return self::ALTO_ZOCALO[$codigo] ?? throw new SerieSinConstantes("Zócalo/cabezal '{$codigo}' inválido.");
    }

    private function burlete(LineasAbertura $lineas, int $espesor, float $largo): void
    {
        [$codigo, $factor] = match (true) {
            $espesor <= 4 => ['100105', 1.0],
            $espesor === 5 => ['100106', 1.0],
            $espesor === 6 => ['100107', 1.0],
            $espesor <= 8 => ['100110', 1.0],
            $espesor <= 10 => ['100109', 1.0],
            $espesor <= 15 => ['100111', 2.0],
            $espesor <= 17 => ['100110', 2.0],
            $espesor === 18 => ['100109', 2.0],
            default => ['100110', 1.0],
        };

        $this->accesorio($lineas, $codigo, $largo * $factor, 'HOJA');
    }

    private function pesoHoja(LineasAbertura $lineas, int $cantidad): int
    {
        $kgHojas = 0.0;
        foreach ($lineas->barras as $barra) {
            if ($barra['parte'] === 'HOJA') {
                $kgHojas += $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];
            }
        }

        return (int) round(($cantidad > 0 ? $kgHojas / $cantidad : 0.0) + $this->kgVidrio($lineas));
    }

    private function kgVidrio(LineasAbertura $lineas): float
    {
        $kg = 0.0;
        foreach ($lineas->vidrios as $vidrio) {
            $espesorTotal = ($vidrio['tipo'] ?? 'simple') === 'dvh' ? 12.0 : 4.0;
            $kg += $vidrio['anchoM'] * $vidrio['altoM'] * $espesorTotal * 2.5 * $vidrio['cantidad'];
        }

        return $kg;
    }

    private function codigoRodamiento(int $pesoHoja, string $zocalo, string $tipoRueda): string
    {
        $tipo = strtoupper(trim($tipoRueda));

        if ($tipo === 'RULEMAN') {
            if ($zocalo === '4504') {
                throw new SerieSinConstantes('No hay rueda de ruleman para la serie 25 baja.');
            }

            return match (true) {
                $pesoHoja < 20 => '230445',
                $pesoHoja < 50 => '230235',
                $pesoHoja < 100 => '230240',
                default => '230240',
            };
        }

        if ($tipo === 'AGUJA') {
            return $pesoHoja < 100 ? '230288' : '230288';
        }

        // CASQUILLO
        if ($zocalo === '4504') {
            return '230448';
        }

        return $pesoHoja < 20 ? '230444' : '230444';
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
