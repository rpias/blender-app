<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: opciones_parametro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $opcion
 */
class OpcionesParametro extends Model
{
    protected $table = 'opciones_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
