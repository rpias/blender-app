<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: perfil
 *
 * @property mixed $id
 * @property mixed $Numero
 * @property mixed $Peso
 * @property mixed $Descripcion
 * @property mixed $esTubular
 * @property mixed $codigoAlternativo
 * @property mixed $minLargoUtilizable
 * @property mixed $comentario
 * @property mixed $descuentoConector
 * @property mixed $rpt
 * @property mixed $premarcoPorDefecto
 * @property mixed $tapajuntasPorDefecto
 * @property mixed $marcoConTapajuntasIncluido
 * @property mixed $tieneCurva
 * @property mixed $tieneCavaParaTornillo
 * @property mixed $esSimetrico
 * @property mixed $forma
 * @property mixed $diametroMatriz
 * @property mixed $anchoPerfil
 */
class Perfil extends Model
{
    protected $table = 'perfil';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'Peso' => 'float',
            'esTubular' => 'integer',
            'minLargoUtilizable' => 'integer',
            'descuentoConector' => 'integer',
            'rpt' => 'integer',
            'marcoConTapajuntasIncluido' => 'integer',
            'tieneCurva' => 'integer',
            'tieneCavaParaTornillo' => 'integer',
            'esSimetrico' => 'integer',
            'anchoPerfil' => 'float',
        ];
    }
}
