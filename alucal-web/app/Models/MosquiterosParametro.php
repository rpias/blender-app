<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: mosquiteros_parametro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $mosquitero
 */
class MosquiterosParametro extends Model
{
    protected $table = 'mosquiteros_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
