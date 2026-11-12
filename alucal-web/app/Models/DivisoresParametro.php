<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: divisores_parametro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $divisor
 */
class DivisoresParametro extends Model
{
    protected $table = 'divisores_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
