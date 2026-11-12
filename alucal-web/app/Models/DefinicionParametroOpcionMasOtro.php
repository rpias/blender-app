<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: definicion_parametro_opcion_mas_otro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $idDefParametroOptions
 * @property mixed $idDefParametroOtro
 * @property mixed $optionsDeVisibilidad
 */
class DefinicionParametroOpcionMasOtro extends Model
{
    protected $table = 'definicion_parametro_opcion_mas_otro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefParametroOptions' => 'integer',
            'idDefParametroOtro' => 'integer',
        ];
    }
}
