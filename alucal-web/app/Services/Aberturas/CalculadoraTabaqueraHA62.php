<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Tabaquera de la línea HA 62 (hoja que se abre sobre el marco, tipo banderola).
 *
 * Port de AberturaServiceTabaquera.getTabaqueraHA62 / _SinRPT
 * (AberturaServiceTabaquera.java:706-798) con el helper agregarCierreTabaquera
 * (AberturaServiceTabaquera.java:861-895).
 *
 * No incluye: uniblock mix, premarco/tapajuntas ni grampas.
 */
class CalculadoraTabaqueraHA62
{
    public const TIPOLOGIA = 12;

    private const CONTRAVIDRIO_RECTO = 'ADR 1655';

    private const CONTRAVIDRIO_CURVO = [
        14 => ['ADR 917', 'ADR 918'],
        22 => ['ADR 920', 'ADR 921'],
        28 => ['ADR 1174', 'ADR 1175'],
        34 => ['ADR 914', 'ADR 915'],
        40 => ['ADR 911', 'ADR 912'],
    ];

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, contravidrios: string,
     *     cierre: string, colorHerrajes: string, espesorVidrio: int,
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

        $contravidrios = strtoupper((string) ($datos['contravidrios'] ?? 'RECTOS'));
        $cierre = strtoupper((string) ($datos['cierre'] ?? 'SAPITO'));
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'BLANCO'));
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);

        [$contraHorizontal, $contraVertical, $burleteUno, $burleteDos] = $this->contravidriosYBurletes($espesor, $contravidrios);

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco (ADR 1470 en HA 62) y hoja ADR 1471.
        $lineas->agregarBarra('ADR 1470', $peso('ADR 1470'), 2 * $cantidad, $ancho, 'MARCO');
        $lineas->agregarBarra('ADR 1470', $peso('ADR 1470'), 2 * $cantidad, $alto, 'MARCO');

        $lineas->agregarBarra('ADR 1471', $peso('ADR 1471'), 2 * $cantidad, $ancho - 52, 'HOJA');
        $lineas->agregarBarra('ADR 1471', $peso('ADR 1471'), 2 * $cantidad, $alto - 52, 'HOJA');

        $lineas->agregarBarra($contraHorizontal, $peso($contraHorizontal), 2 * $cantidad, $ancho - 153, 'HOJA');
        $lineas->agregarBarra($contraVertical, $peso($contraVertical), 2 * $cantidad, $alto - 191, 'HOJA');

        // Accesorios.
        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230419', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230376', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230716', 2.0 * ($anchoM + $altoM) * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230419', 4.0 * $cantidad, 'HOJA');

        $this->cierre($lineas, $cierre, $color, $cantidad);

        $this->accesorio($lineas, 'AA258', 2.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230353', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230728', (2.0 * $anchoM + 2.0 * $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, $burleteUno, 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, $burleteDos, 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');

        // Vidrio.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 1 * $cantidad,
                anchoM: round($anchoM - 0.169, 3),
                altoM: round($altoM - 0.169, 3),
            );
        }

        return $lineas;
    }

    private function cierre(LineasAbertura $lineas, string $cierre, string $color, int $cantidad): void
    {
        if ($cierre === 'SAPITO') {
            $this->accesorio($lineas, $color === 'NEGRO' ? '230281' : '230282', 1.0 * $cantidad, 'HOJA');
            $this->accesorio($lineas, 'H-87', 2.0 * $cantidad, 'HOJA');

            return;
        }

        if ($cierre === 'SIMPLÓN NEWTON' || $cierre === 'SIMPLON NEWTON') {
            $this->accesorio($lineas, '100460', 1.0 * $cantidad, 'HOJA');

            return;
        }

        if ($cierre === 'SIMPLÓN TRADICIONAL' || $cierre === 'SIMPLON TRADICIONAL') {
            $this->accesorio($lineas, '100462', 1.0 * $cantidad, 'HOJA');

            return;
        }

        throw new SerieSinConstantes("Cierre '{$cierre}' inválido para tabaquera HA 62.");
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
                $espesor <= 10 => self::CONTRAVIDRIO_RECTO,
                $espesor <= 14 => 'ADR 1295',
                $espesor <= 22 => 'ADR 916',
                $espesor <= 26 => 'ADR 919',
                $espesor <= 28 => 'ADR 1489',
                $espesor <= 32 => 'ADR 1163',
                $espesor <= 36 => 'ADR 913',
                $espesor <= 40 => 'ADR 910',
                default => throw new SerieSinConstantes('La tabaquera HA 62 no acepta vidrios de más de 40 mm.'),
            };

            return [$contravidrio, $contravidrio, $burleteUno, $burleteDos];
        }

        if ($tipo !== 'CURVOS') {
            throw new SerieSinConstantes("Contravidrios '{$tipo}' inválidos para tabaquera HA 62.");
        }

        if ($espesor < 14) {
            throw new SerieSinConstantes('Con menos de 14 mm de vidrio hay que usar contravidrios rectos.');
        }

        foreach (self::CONTRAVIDRIO_CURVO as $limite => $codigos) {
            if ($espesor <= $limite) {
                return [$codigos[0], $codigos[1], $burleteUno, $burleteDos];
            }
        }

        throw new SerieSinConstantes('La tabaquera HA 62 no acepta vidrios de más de 40 mm.');
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
