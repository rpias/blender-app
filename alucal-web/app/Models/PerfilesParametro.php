<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: perfiles_parametro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $perfil
 */
class PerfilesParametro extends Model
{
    protected $table = 'perfiles_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
