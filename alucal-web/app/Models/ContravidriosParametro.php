<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: contravidrios_parametro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $contravidrio
 */
class ContravidriosParametro extends Model
{
    protected $table = 'contravidrios_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
