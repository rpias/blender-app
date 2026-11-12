<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Oscilobatiente de la línea HA 62 (una hoja que abre y bascula).
 *
 * Port de AberturaServiceOsciloBatiente.getOsciloBatienteHA62
 * (AberturaServiceOsciloBatiente.java:477-552), con la selección de kit por
 * ancho y peso de hoja de getCodigoKitOscilobatiente
 * (AberturaServiceOsciloBatiente.java:810-898).
 *
 * No incluye: uniblock mix, premarco/tapajuntas ni grampas.
 */
class CalculadoraOscilobatienteHA62
{
    public const TIPOLOGIA = 7;

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, marco: string,
     *     contravidrios: string, colorHerrajes: string, espesorVidrio: int,
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

        $marco = (string) ($datos['marco'] ?? 'ADR 1470');
        $contravidrios = strtoupper((string) ($datos['contravidrios'] ?? 'RECTOS'));
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'BLANCO'));
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);

        [$contraHorizontal, $contraVertical, $burleteUno, $burleteDos] = $this->contravidriosYBurletes($espesor, $contravidrios);

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco y hoja.
        $lineas->agregarBarra($marco, $peso($marco), 2 * $cantidad, $ancho, 'MARCO');
        $lineas->agregarBarra($marco, $peso($marco), 2 * $cantidad, $alto, 'MARCO');

        $lineas->agregarBarra('ADR 1471', $peso('ADR 1471'), 2 * $cantidad, $ancho - 52, 'HOJA');
        $lineas->agregarBarra('ADR 1471', $peso('ADR 1471'), 2 * $cantidad, $alto - 52, 'HOJA');

        $lineas->agregarBarra($contraHorizontal, $peso($contraHorizontal), 2 * $cantidad, $ancho - 153, 'HOJA');
        $lineas->agregarBarra($contraVertical, $peso($contraVertical), 2 * $cantidad, $alto - 191, 'HOJA');

        // Vidrio (necesario para el peso de hoja que elige el kit).
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 1 * $cantidad,
                anchoM: round($anchoM - 0.169, 3),
                altoM: round($altoM - 0.169, 3),
            );
        }

        // Accesorios: el kit depende de ancho y peso de hoja.
        $anchoHoja = (int) round($ancho - 52);
        $pesoHoja = $this->pesoHoja($lineas, $cantidad);
        $kit = $this->kit(esCoplanar: $marco === 'ADR 1470', esBlanco: $color !== 'NEGRO', anchoHoja: $anchoHoja, pesoHoja: $pesoHoja);

        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, $kit, 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230419', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230376', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230716', 2.0 * ($anchoM + $altoM) * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230419', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230353', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230728', (2.0 * $anchoM + 2.0 * $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, $burleteUno, 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, $burleteDos, 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');

        return $lineas;
    }

    private function pesoHoja(LineasAbertura $lineas, int $cantidad): int
    {
        $kg = 0.0;

        foreach ($lineas->barras as $barra) {
            if ($barra['parte'] === 'HOJA') {
                $kg += $barra['cantidad'] * ($barra['largoMM'] / 1000.0) * $barra['pesoKgPorMetro'];
            }
        }

        foreach ($lineas->vidrios as $vidrio) {
            $kg += $vidrio['anchoM'] * $vidrio['altoM'] * 4.0 * 2.5 * $vidrio['cantidad'];
        }

        return max(1, (int) round($cantidad > 0 ? $kg / $cantidad : $kg));
    }

    private function kit(bool $esCoplanar, bool $esBlanco, int $anchoHoja, int $pesoHoja): string
    {
        if ($esCoplanar) {
            if ($pesoHoja > 110) {
                throw new SerieSinConstantes("El peso máximo del oscilo coplanar es 110 kg y la hoja pesa {$pesoHoja} kg.");
            }
            if ($anchoHoja < 375) {
                throw new SerieSinConstantes('No hay kit de oscilo coplanar para hojas de menos de 375 mm.');
            }

            return match (true) {
                $anchoHoja < 470 => $esBlanco ? '230678' : '230677',
                $anchoHoja < 580 => $esBlanco ? '230680' : '230679',
                $anchoHoja <= 1700 => $esBlanco ? '230682' : '230681',
                default => throw new SerieSinConstantes('No hay kit de oscilo coplanar para hojas de más de 1.700 mm.'),
            };
        }

        if ($anchoHoja <= 1000 && $pesoHoja > 130) {
            throw new SerieSinConstantes("Para hojas de hasta 1.000 mm el peso máximo es 130 kg y esta pesa {$pesoHoja} kg.");
        }

        if ($anchoHoja > 1000 && $pesoHoja > 80) {
            throw new SerieSinConstantes("Para hojas de más de 1.000 mm el peso máximo es 80 kg y esta pesa {$pesoHoja} kg.");
        }

        if ($anchoHoja < 370) {
            throw new SerieSinConstantes('No hay kit de oscilo escalonado para hojas de menos de 370 mm.');
        }

        return match (true) {
            $anchoHoja < 500 => $esBlanco ? '230672' : '230671',
            $anchoHoja < 600 => $esBlanco ? '230674' : '230673',
            $anchoHoja < 1200 => $esBlanco ? '230676' : '230675',
            $anchoHoja <= 1700 => $esBlanco ? '230742' : '230741',
            default => throw new SerieSinConstantes('No hay kit de oscilo escalonado para hojas de más de 1.700 mm.'),
        };
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function contravidriosYBurletes(int $espesor, string $tipo): array
    {
        $burleteUno = '193760';
        $burleteDos = match (true) {
            $espesor <= 7 => '230711',
            $espesor === 8 => '230710',
            $espesor === 9 => '230714',
            $espesor === 10 => '230807',
            default => '230711',
        };

        if ($tipo === 'RECTOS') {
            $contravidrio = match (true) {
                $espesor <= 10 => 'ADR 1655',
                $espesor <= 14 => 'ADR 1295',
                $espesor <= 22 => 'ADR 916',
                $espesor <= 26 => 'ADR 919',
                $espesor <= 28 => 'ADR 1489',
                $espesor <= 32 => 'ADR 1163',
                $espesor <= 36 => 'ADR 913',
                $espesor <= 40 => 'ADR 910',
                default => throw new SerieSinConstantes('El oscilobatiente HA 62 no acepta vidrios de más de 40 mm.'),
            };

            return [$contravidrio, $contravidrio, $burleteUno, $burleteDos];
        }

        if ($tipo !== 'CURVOS') {
            throw new SerieSinConstantes("Contravidrios '{$tipo}' inválidos para oscilobatiente HA 62.");
        }

        if ($espesor < 14) {
            throw new SerieSinConstantes('Con menos de 14 mm de vidrio hay que usar contravidrios rectos.');
        }

        foreach ([14 => ['ADR 917', 'ADR 918'], 22 => ['ADR 920', 'ADR 921'], 28 => ['ADR 1174', 'ADR 1175'], 34 => ['ADR 914', 'ADR 915'], 40 => ['ADR 911', 'ADR 912']] as $limite => $codigos) {
            if ($espesor <= $limite) {
                return [$codigos[0], $codigos[1], $burleteUno, $burleteDos];
            }
        }

        throw new SerieSinConstantes('El oscilobatiente HA 62 no acepta vidrios de más de 40 mm.');
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
