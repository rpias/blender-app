<?php

namespace App\Servicios\Exportacion;

/**
 * Optimización de corte sobre barras comerciales (spec 0018).
 *
 * Agrupa los tramos por perfil y los reparte en barras de largo fijo con un
 * algoritmo first-fit decreasing: ordena de mayor a menor y coloca cada tramo
 * en la primera barra donde entra. Es determinista y fácil de explicar en el
 * taller; no busca el óptimo absoluto.
 */
class OptimizacionCorte
{
    public const LARGO_BARRA_MM = 6000;

    public const KERF_MM = 3;

    /**
     * @param  list<array{codigo: string, largoMM: float, cantidad: int}>  $perfiles
     * @return list<array{
     *     codigo: string,
     *     barras: list<array{cortes: list<float>, usadoMM: float, sobranteMM: float}>,
     *     totalBarras: int,
     *     sobranteMM: float,
     *     sobrantePct: float,
     *     noEntran: list<array{largoMM: float, cantidad: int}>
     * }>
     */
    public function para(array $perfiles, int $largoBarra = self::LARGO_BARRA_MM, int $kerf = self::KERF_MM): array
    {
        $porPerfil = [];

        foreach ($perfiles as $fila) {
            $codigo = (string) $fila['codigo'];
            $largo = (float) $fila['largoMM'];
            $cantidad = (int) $fila['cantidad'];

            if ($codigo === '' || $largo <= 0 || $cantidad <= 0) {
                continue;
            }

            $porPerfil[$codigo] ??= ['piezas' => [], 'noEntran' => []];

            if ($largo + $kerf > $largoBarra) {
                $porPerfil[$codigo]['noEntran'][] = ['largoMM' => $largo, 'cantidad' => $cantidad];

                continue;
            }

            for ($i = 0; $i < $cantidad; $i++) {
                $porPerfil[$codigo]['piezas'][] = $largo;
            }
        }

        $resultado = [];

        foreach ($porPerfil as $codigo => $datos) {
            $resultado[] = $this->empacar($codigo, $datos['piezas'], $datos['noEntran'], $largoBarra, $kerf);
        }

        usort($resultado, fn (array $a, array $b) => $a['codigo'] <=> $b['codigo']);

        return $resultado;
    }

    /**
     * @param  list<float>  $piezas
     * @param  list<array{largoMM: float, cantidad: int}>  $noEntran
     * @return array<string, mixed>
     */
    private function empacar(string $codigo, array $piezas, array $noEntran, int $largoBarra, int $kerf): array
    {
        rsort($piezas);

        $barras = [];

        foreach ($piezas as $pieza) {
            $colocada = false;

            foreach ($barras as &$barra) {
                if ($largoBarra >= $barra['usadoMM'] + $pieza + $kerf) {
                    $barra['cortes'][] = $pieza;
                    $barra['usadoMM'] += $pieza + $kerf;
                    $colocada = true;
                    break;
                }
            }

            unset($barra);

            if (! $colocada) {
                $barras[] = ['cortes' => [$pieza], 'usadoMM' => $pieza + $kerf];
            }
        }

        $desperdicio = 0.0;

        foreach ($barras as &$barra) {
            $barra['sobranteMM'] = round($largoBarra - $barra['usadoMM'], 1);
            $barra['usadoMM'] = round($barra['usadoMM'], 1);
            $desperdicio += $barra['sobranteMM'];
        }

        unset($barra);

        $totalBarras = count($barras);

        return [
            'codigo' => $codigo,
            'barras' => $barras,
            'totalBarras' => $totalBarras,
            'sobranteMM' => round($desperdicio, 1),
            'sobrantePct' => $totalBarras > 0
                ? round($desperdicio / ($totalBarras * $largoBarra) * 100, 1)
                : 0.0,
            'noEntran' => $noEntran,
        ];
    }
}
