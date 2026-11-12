<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: definicion_parametro
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $tipoDeDato
 * @property mixed $categoria
 * @property mixed $cambiaCantVidrios
 * @property mixed $descripcion
 */
class DefinicionParametro extends Model
{
    protected $table = 'definicion_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cambiaCantVidrios' => 'integer',
        ];
    }
}
