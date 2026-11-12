<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: definicion_parametro_integer
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $cotaInferior
 * @property mixed $cotaSuperior
 */
class DefinicionParametroInteger extends Model
{
    protected $table = 'definicion_parametro_integer';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
