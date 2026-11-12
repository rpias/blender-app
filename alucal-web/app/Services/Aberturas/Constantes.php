<?php

namespace App\Services\Aberturas;

use App\Models\Perfil;
use Illuminate\Support\Facades\DB;

/**
 * Constantes de corte por tipología-serie y medidas de perfil.
 *
 * Reemplaza las constantes que en el original estaban hardcodeadas dentro de
 * cada método de AberturaService*.
 */
class Constantes
{
    /** @var array<string, float> */
    private array $cache = [];

    public function de(int $idTipologia, int $idSerie, string $clave, ?float $defecto = null): float
    {
        $llave = "{$idTipologia}:{$idSerie}:{$clave}";

        if (isset($this->cache[$llave])) {
            return $this->cache[$llave];
        }

        $valor = DB::table('constantes_calculo')
            ->where('idTipologia', $idTipologia)
            ->where('idSerie', $idSerie)
            ->where('clave', $clave)
            ->value('valor');

        if ($valor === null) {
            if ($defecto === null) {
                throw new SerieSinConstantes(
                    "Falta la constante '{$clave}' para tipología {$idTipologia} / serie {$idSerie}. ".
                    'Agregala en constantes_calculo (ver Alucal_v5.5/_analysis/aberturas.md).'
                );
            }

            $valor = $defecto;
        }

        return $this->cache[$llave] = (float) $valor;
    }

    public function soportada(int $idTipologia, int $idSerie): bool
    {
        return DB::table('constantes_calculo')
            ->where('idTipologia', $idTipologia)
            ->where('idSerie', $idSerie)
            ->exists();
    }

    /**
     * Peso del perfil en kg/m a partir de su código comercial.
     */
    public function pesoPerfil(string $codigo): float
    {
        $peso = Perfil::query()->where('Numero', $codigo)->value('Peso');

        if ($peso === null) {
            throw new SerieSinConstantes("No existe el perfil '{$codigo}' en el catálogo.");
        }

        return (float) $peso;
    }

    /**
     * Ancho del perfil en mm (contravidrio, divisor) tomado de perfil_medidas.
     */
    public function anchoPerfil(string $codigo): float
    {
        $ancho = DB::table('perfil_medidas')->where('codigo', $codigo)->value('anchoMM');

        if ($ancho === null) {
            throw new SerieSinConstantes(
                "Falta el ancho (mm) del perfil '{$codigo}' en perfil_medidas."
            );
        }

        return (float) $ancho;
    }
}
