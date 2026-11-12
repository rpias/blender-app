<?php

namespace App\Services\Aberturas;

use App\Models\AberturaSimple;
use Illuminate\Support\Facades\DB;

/**
 * Lee los parámetros particulares guardados por el Java (modelo EAV).
 *
 * Cada fila de valor_parametro_particular apunta a una definición y tiene su
 * valor tipado en una tabla hermana con el mismo id.
 */
class LectorParametros
{
    /** @var array<string, string> tipoDeDato => tabla del valor */
    private const TABLAS = [
        'PERFIL' => 'valor_parametro_particular_perfil',
        'ACCESORIO' => 'valor_parametro_particular_accesorio',
        'OPTIONS' => 'valor_parametro_particular_options',
        'OPTIONS_MAS_OTRO' => 'valor_parametro_particular_options_mas_otro',
        'PREMARCO' => 'valor_parametro_particular_premarco',
        'ACRISTALAMIENTO' => 'valor_parametro_particular_acristalamiento',
        'DIVISORES' => 'valor_parametro_particular_divisores',
        'CONTRAVIDRIOS' => 'valor_parametro_particular_contravidrios',
        'MOSQUITERO' => 'valor_parametro_particular_mosquitero',
        'UNIBLOCK_MIX' => 'valor_parametro_particular_uniblock_mix',
        'TIPO_CORREDIZA' => 'valor_parametro_particular_tipo_corrediza',
        'CIERRES_CORREDIZA' => 'valor_parametro_particular_cierres_corrediza',
        'INTEGER' => 'valor_parametro_particular_integer',
    ];

    /**
     * @return list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>
     */
    public function para(AberturaSimple $abertura): array
    {
        $filas = DB::table('valor_parametro_particular as v')
            ->join('definicion_parametro as d', 'd.id', '=', 'v.idDefParamParticular')
            ->where('v.idAberturaSimple', $abertura->id)
            ->get(['v.id', 'v.idDefParamParticular', 'd.nombre', 'd.tipoDeDato']);

        if ($filas->isEmpty()) {
            return [];
        }

        $porTipo = $filas->groupBy('tipoDeDato');
        $valores = [];

        foreach ($porTipo as $tipo => $grupo) {
            $tabla = self::TABLAS[$tipo] ?? null;
            if ($tabla === null || ! DB::getSchemaBuilder()->hasTable($tabla)) {
                continue;
            }

            $ids = $grupo->pluck('id')->all();
            $registros = DB::table($tabla)->whereIn('id', $ids)->get();

            foreach ($registros as $registro) {
                $valores[(int) $registro->id] = (array) $registro;
            }
        }

        return $filas->map(fn ($fila) => [
            'def' => (int) $fila->idDefParamParticular,
            'nombre' => (string) $fila->nombre,
            'tipo' => (string) $fila->tipoDeDato,
            'valor' => $valores[(int) $fila->id] ?? [],
        ])->values()->all();
    }

    /**
     * Busca un parámetro por nombre de definición (comparación laxa).
     *
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}|null
     */
    public function buscar(array $parametros, string $nombre): ?array
    {
        foreach ($parametros as $parametro) {
            if (strcasecmp(trim($parametro['nombre']), trim($nombre)) === 0) {
                return $parametro;
            }
        }

        return null;
    }

    /**
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}|null
     */
    public function buscarPorTipo(array $parametros, string $tipo): ?array
    {
        foreach ($parametros as $parametro) {
            if ($parametro['tipo'] === $tipo) {
                return $parametro;
            }
        }

        return null;
    }
}
