<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: precio_metro_lineal_premarco
 *
 * @property mixed $id
 * @property mixed $perfil
 * @property mixed $precio
 */
class PrecioMetroLinealPremarco extends Model
{
    protected $table = 'precio_metro_lineal_premarco';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio' => 'float',
        ];
    }
}
