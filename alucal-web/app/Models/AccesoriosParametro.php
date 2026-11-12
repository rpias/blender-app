<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: accesorios_parametro
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $accesorio
 */
class AccesoriosParametro extends Model
{
    protected $table = 'accesorios_parametro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
        ];
    }
}
