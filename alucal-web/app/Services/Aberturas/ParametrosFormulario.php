<?php

namespace App\Services\Aberturas;

use App\Models\Perfil;
use App\Models\Serie;
use App\Models\TipoVidrio;
use App\Models\VidrioSinCamara;
use Illuminate\Support\Facades\DB;

/**
 * Opciones de formulario para cargar una abertura.
 *
 * Las opciones salen de las mismas tablas que usa el motor Java: las
 * definiciones de parámetro asignadas a la combinación tipología-serie y sus
 * valores admitidos (perfiles, divisores, contravidrios).
 */
class ParametrosFormulario
{
    /**
     * Series con constantes de cálculo cargadas para una tipología.
     *
     * @return list<array{id: int, nombre: string}>
     */
    public function series(int $idTipologia): array
    {
        $ids = DB::table('constantes_calculo')
            ->where('idTipologia', $idTipologia)
            ->distinct()
            ->orderBy('idSerie')
            ->pluck('idSerie');

        return Serie::query()
            ->whereIn('id', $ids)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($serie) => ['id' => (int) $serie->id, 'nombre' => (string) $serie->nombre])
            ->all();
    }

    /**
     * Opciones por tipo de parámetro para una combinación tipología-serie.
     *
     * @return array{perfiles: list<array{codigo: string, descripcion: string}>, divisores: list<string>, contravidrios: list<string>}
     */
    public function opciones(int $idTipologia, int $idSerie): array
    {
        $definiciones = DB::table('def_param_tipologia_serie as dts')
            ->join('definicion_parametro as d', 'd.id', '=', 'dts.idDefParam')
            ->where('dts.idTipologia', $idTipologia)
            ->where('dts.idSerie', $idSerie)
            ->get(['d.id', 'd.tipoDeDato'])
            ->unique('id');

        $idsPerfil = $definiciones->where('tipoDeDato', 'PERFIL')->pluck('id');
        $idsDivisor = $definiciones->where('tipoDeDato', 'DIVISORES')->pluck('id');
        $idsContravidrio = $definiciones->where('tipoDeDato', 'CONTRAVIDRIOS')->pluck('id');

        $codigosPerfil = DB::table('perfiles_parametro')->whereIn('idDefinicionParametro', $idsPerfil)->pluck('perfil')->unique();
        $codigosDivisor = DB::table('divisores_parametro')->whereIn('idDefinicionParametro', $idsDivisor)->pluck('divisor')->unique();
        $codigosContravidrio = DB::table('contravidrios_parametro')->whereIn('idDefinicionParametro', $idsContravidrio)->pluck('contravidrio')->unique();

        $descripciones = Perfil::query()
            ->whereIn('Numero', $codigosPerfil)
            ->pluck('Descripcion', 'Numero');

        return [
            'perfiles' => $codigosPerfil
                ->map(fn ($codigo) => [
                    'codigo' => (string) $codigo,
                    'descripcion' => (string) ($descripciones[$codigo] ?? $codigo),
                ])
                ->values()
                ->all(),
            'divisores' => $codigosDivisor->map(fn ($codigo) => (string) $codigo)->values()->all(),
            'contravidrios' => $codigosContravidrio->map(fn ($codigo) => (string) $codigo)->values()->all(),
        ];
    }

    /**
     * Vidrios simples disponibles para presupuestar.
     *
     * @return list<array{id: int, etiqueta: string, precio: float}>
     */
    public function vidrios(): array
    {
        $tipos = TipoVidrio::query()->pluck('tipo', 'id');

        return VidrioSinCamara::query()
            ->orderBy('id')
            ->get(['id', 'idTipo', 'precio'])
            ->map(fn ($vidrio) => [
                'id' => (int) $vidrio->id,
                'etiqueta' => trim(($tipos[$vidrio->idTipo] ?? 'VIDRIO').' · U$S '.number_format((float) $vidrio->precio, 2, ',', '.')),
                'precio' => (float) $vidrio->precio,
            ])
            ->all();
    }
}
