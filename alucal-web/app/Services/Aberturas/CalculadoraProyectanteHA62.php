<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Proyectante de la línea HA 62 (una hoja, abre hacia afuera).
 *
 * Port de AberturaServiceProyDesliz.getProyDeslizHA62
 * (AberturaServiceProyDesliz.java:643-719), con contravidrios y burletes de
 * AberturaService.getContravidriosYBurletesBatienteHA62
 * (AberturaService.java:3005-3095).
 *
 * Alcance: espesores de 4 a 10 mm (contravidrios rectos ADR 1655) y curvos
 * desde 14 mm. No incluye uniblock mix, premarco/tapajuntas ni grampas.
 */
class CalculadoraProyectanteHA62
{
    public const TIPOLOGIA = 10;

    private const CONTRAVIDRIO_RECTO = 'ADR 1655';

    private const CONTRAVIDRIO_CURVO = [
        14 => ['ADR 917', 'ADR 918'],
        22 => ['ADR 920', 'ADR 921'],
        28 => ['ADR 1174', 'ADR 1175'],
        34 => ['ADR 914', 'ADR 915'],
        40 => ['ADR 911', 'ADR 912'],
    ];

    /** Burlete interior según espesor (mapa del original para 1-10 mm). */
    private const BURLETE = [
        4 => '230711', 5 => '230711', 6 => '230711', 7 => '230711',
        8 => '230710', 9 => '230714', 10 => '230807',
    ];

    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float, altoMM: float, cantidad?: int, contravidrios: string,
     *     colorHerrajes: string, espesorVidrio: int, vidrio: array<string, mixed>|null
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
        $color = strtoupper((string) ($datos['colorHerrajes'] ?? 'BLANCO'));
        $espesor = (int) ($datos['espesorVidrio'] ?? 4);

        [$contraHorizontal, $contraVertical, $burleteUno, $burleteDos] = $this->contravidriosYBurletes($espesor, $contravidrios);

        $peso = fn (string $codigo): float => $this->constantes->pesoPerfil($codigo);
        $lineas = new LineasAbertura;

        // Marco a 45°.
        $lineas->agregarBarra('ADR 1483', $peso('ADR 1483'), 2 * $cantidad, $ancho, 'MARCO');
        $lineas->agregarBarra('ADR 1483', $peso('ADR 1483'), 2 * $cantidad, $alto, 'MARCO');

        // Hoja a 45°.
        $lineas->agregarBarra('ADR 1484', $peso('ADR 1484'), 2 * $cantidad, $ancho - 46, 'HOJA');
        $lineas->agregarBarra('ADR 1484', $peso('ADR 1484'), 2 * $cantidad, $alto - 46, 'HOJA');

        // Contravidrios (horizontal y vertical llevan medidas distintas).
        $lineas->agregarBarra($contraHorizontal, $peso($contraHorizontal), 2 * $cantidad, $ancho - 164, 'HOJA');
        $lineas->agregarBarra($contraVertical, $peso($contraVertical), 2 * $cantidad, $alto - 202, 'HOJA');

        // Accesorios.
        $this->accesorio($lineas, '230049', 2.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230415', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '230376', 4.0 * $cantidad, 'MARCO');
        $this->accesorio($lineas, '193780', 2.0 * ($anchoM + $altoM) * $cantidad, 'MARCO');
        $this->accesorio($lineas, $color === 'NEGRO' ? '230306' : '230134', 1.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '192804', 2.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '193285', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '230353', 4.0 * $cantidad, 'HOJA');
        $this->accesorio($lineas, '193790', 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, $burleteUno, 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');
        $this->accesorio($lineas, $burleteDos, 2.0 * ($anchoM + $altoM) * $cantidad, 'HOJA');

        // Vidrio.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $lineas->agregarVidrioPayload(
                $vidrio,
                cantidad: 1 * $cantidad,
                anchoM: round($anchoM - 0.18, 3),
                altoM: round($altoM - 0.18, 3),
            );
        }

        return $lineas;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function contravidriosYBurletes(int $espesor, string $tipo): array
    {
        $burleteUno = '193760';
        $burleteDos = self::BURLETE[$espesor] ?? '230711';

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
                default => throw new SerieSinConstantes('El proyectante HA 62 no acepta vidrios de más de 40 mm.'),
            };

            return [$contravidrio, $contravidrio, $burleteUno, $burleteDos];
        }

        if ($tipo !== 'CURVOS') {
            throw new SerieSinConstantes("Contravidrios '{$tipo}' inválidos para proyectante HA 62.");
        }

        if ($espesor < 14) {
            throw new SerieSinConstantes('Con menos de 14 mm de vidrio hay que usar contravidrios rectos.');
        }

        $par = null;
        foreach (self::CONTRAVIDRIO_CURVO as $limite => $codigos) {
            if ($espesor <= $limite) {
                $par = $codigos;
                break;
            }
        }

        if ($par === null) {
            throw new SerieSinConstantes('El proyectante HA 62 no acepta vidrios de más de 40 mm.');
        }

        return [$par[0], $par[1], $burleteUno, $burleteDos];
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
