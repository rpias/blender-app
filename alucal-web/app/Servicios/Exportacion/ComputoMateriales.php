<?php

namespace App\Servicios\Exportacion;

use App\Models\Accesorio;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\Perfil;

/**
 * Cómputo de materiales de una obra (spec 0014).
 *
 * Agrupa lo que ya quedó guardado en cada abertura al calcular su precio:
 * perfiles por medida de corte, accesorios por código y vidrios por medida.
 */
class ComputoMateriales
{
    /**
     * @return array{
     *     perfiles: list<array{codigo: string, descripcion: string, largoMM: float, cantidad: int, metros: float, kilos: float}>,
     *     accesorios: list<array{codigo: string, descripcion: string, cantidad: float}>,
     *     vidrios: list<array{descripcion: string, cantidad: int, anchoM: float, altoM: float, m2: float}>,
     *     kilos: float,
     *     aberturas: int
     * }
     */
    public function para(Obra $obra): array
    {
        $perfiles = [];
        $accesorios = [];
        $vidrios = [];
        $kilos = 0.0;
        $cantidadAberturas = 0;

        foreach ($obra->elementos as $elemento) {
            foreach ($this->aberturasDe($elemento) as $abertura) {
                $lineas = $abertura['lineas'];

                if (! is_array($lineas) || $lineas === []) {
                    continue;
                }

                $cantidadAberturas++;

                foreach ($lineas['barras'] ?? [] as $barra) {
                    $this->sumarBarra($perfiles, $barra);
                }

                foreach ($lineas['accesorios'] ?? [] as $accesorio) {
                    $this->sumarAccesorio($accesorios, $accesorio);
                }

                foreach ($lineas['vidrios'] ?? [] as $vidrio) {
                    $this->sumarVidrio($vidrios, $vidrio);
                }

                $kilos += (float) ($lineas['kilos'] ?? 0);
            }
        }

        return $this->conDescripciones([
            'perfiles' => $this->ordenarPerfiles($perfiles),
            'accesorios' => $this->ordenarAccesorios($accesorios),
            'vidrios' => $this->ordenarVidrios($vidrios),
            'kilos' => round($kilos, 2),
            'aberturas' => $cantidadAberturas,
        ]);
    }

    /**
     * Aberturas simples del elemento, o los hijos si es un vano compuesto.
     *
     * @return list<array{lineas: mixed}>
     */
    private function aberturasDe(ElementoObra $elemento): array
    {
        if ($elemento->tipoElemento === 'ABERTURA_COMPUESTA') {
            return $elemento->hijos()->with('aberturaSimple')->get()
                ->map(fn (ElementoObra $hijo) => ['lineas' => $hijo->aberturaSimple?->lineas])
                ->all();
        }

        return [['lineas' => $elemento->aberturaSimple?->lineas]];
    }

    /**
     * @param  array<string, array<string, mixed>>  $perfiles
     * @param  array<string, mixed>  $barra
     */
    private function sumarBarra(array &$perfiles, array $barra): void
    {
        $codigo = (string) ($barra['perfil'] ?? '');
        $largo = (float) ($barra['largoMM'] ?? 0);
        $cantidad = (int) ($barra['cantidad'] ?? 0);

        if ($codigo === '' || $largo <= 0 || $cantidad <= 0) {
            return;
        }

        $clave = $codigo.'|'.$largo;

        $perfiles[$clave] ??= [
            'codigo' => $codigo,
            'descripcion' => '',
            'largoMM' => $largo,
            'cantidad' => 0,
            'metros' => 0.0,
            'kilos' => 0.0,
        ];

        $perfiles[$clave]['cantidad'] += $cantidad;
        $perfiles[$clave]['metros'] += $largo / 1000.0 * $cantidad;
        $perfiles[$clave]['kilos'] += ($largo / 1000.0) * (float) ($barra['pesoKgPorMetro'] ?? 0) * $cantidad;
    }

    /**
     * @param  array<string, array<string, mixed>>  $accesorios
     * @param  array<string, mixed>  $accesorio
     */
    private function sumarAccesorio(array &$accesorios, array $accesorio): void
    {
        $codigo = (string) ($accesorio['codigo'] ?? '');
        $cantidad = (float) ($accesorio['cantidad'] ?? 0);

        if ($codigo === '' || $cantidad <= 0) {
            return;
        }

        $accesorios[$codigo] ??= ['codigo' => $codigo, 'descripcion' => '', 'cantidad' => 0.0];
        $accesorios[$codigo]['cantidad'] += $cantidad;
    }

    /**
     * @param  array<string, array<string, mixed>>  $vidrios
     * @param  array<string, mixed>  $vidrio
     */
    private function sumarVidrio(array &$vidrios, array $vidrio): void
    {
        $ancho = round((float) ($vidrio['anchoM'] ?? 0), 3);
        $alto = round((float) ($vidrio['altoM'] ?? 0), 3);
        $cantidad = (int) ($vidrio['cantidad'] ?? 0);

        if ($ancho <= 0 || $alto <= 0 || $cantidad <= 0) {
            return;
        }

        $descripcion = $this->describirVidrio($vidrio);
        $clave = $descripcion.'|'.$ancho.'x'.$alto;

        $vidrios[$clave] ??= [
            'descripcion' => $descripcion,
            'cantidad' => 0,
            'anchoM' => $ancho,
            'altoM' => $alto,
            'm2' => 0.0,
        ];

        $vidrios[$clave]['cantidad'] += $cantidad;
        $vidrios[$clave]['m2'] += $ancho * $alto * $cantidad;
    }

    /**
     * @param  array<string, mixed>  $vidrio
     */
    private function describirVidrio(array $vidrio): string
    {
        $tipo = mb_strtolower((string) ($vidrio['tipo'] ?? 'simple'));

        if ($tipo === 'dvh') {
            $camara = $vidrio['camara'] ?? $vidrio['espesorCamara'] ?? null;

            return $camara !== null ? "DVH ({$camara} mm cámara)" : 'DVH';
        }

        return $tipo === 'simple' ? 'Vidrio simple' : ucfirst($tipo);
    }

    /**
     * @param  array<string, array<string, mixed>>  $perfiles
     * @return list<array<string, mixed>>
     */
    private function ordenarPerfiles(array $perfiles): array
    {
        $lista = array_values($perfiles);

        usort($lista, fn (array $a, array $b) => [$a['codigo'], $a['largoMM']] <=> [$b['codigo'], $b['largoMM']]);

        foreach ($lista as &$fila) {
            $fila['metros'] = round($fila['metros'], 2);
            $fila['kilos'] = round($fila['kilos'], 2);
        }

        return $lista;
    }

    /**
     * @param  array<string, array<string, mixed>>  $accesorios
     * @return list<array<string, mixed>>
     */
    private function ordenarAccesorios(array $accesorios): array
    {
        $lista = array_values($accesorios);

        usort($lista, fn (array $a, array $b) => $a['codigo'] <=> $b['codigo']);

        foreach ($lista as &$fila) {
            $fila['cantidad'] = round($fila['cantidad'], 2);
        }

        return $lista;
    }

    /**
     * @param  array<string, array<string, mixed>>  $vidrios
     * @return list<array<string, mixed>>
     */
    private function ordenarVidrios(array $vidrios): array
    {
        $lista = array_values($vidrios);

        usort($lista, fn (array $a, array $b) => [$a['descripcion'], $a['anchoM'], $a['altoM']] <=> [$b['descripcion'], $b['anchoM'], $b['altoM']]);

        foreach ($lista as &$fila) {
            $fila['m2'] = round($fila['m2'], 3);
        }

        return $lista;
    }

    /**
     * Completa la descripción de perfiles y accesorios con el catálogo.
     *
     * @param  array<string, mixed>  $computo
     * @return array<string, mixed>
     */
    private function conDescripciones(array $computo): array
    {
        $perfiles = Perfil::query()
            ->whereIn('Numero', array_column($computo['perfiles'], 'codigo'))
            ->pluck('Descripcion', 'Numero');

        $accesorios = Accesorio::query()
            ->whereIn('codigo', array_column($computo['accesorios'], 'codigo'))
            ->pluck('descripcion', 'codigo');

        foreach ($computo['perfiles'] as &$fila) {
            $fila['descripcion'] = (string) ($perfiles[$fila['codigo']] ?? '');
        }

        foreach ($computo['accesorios'] as &$fila) {
            $fila['descripcion'] = (string) ($accesorios[$fila['codigo']] ?? '');
        }

        return $computo;
    }
}
