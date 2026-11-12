<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: precio_metro_lineal_tapajuntas
 *
 * @property mixed $id
 * @property mixed $perfil
 * @property mixed $precio
 */
class PrecioMetroLinealTapajuntas extends Model
{
    protected $table = 'precio_metro_lineal_tapajuntas';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio' => 'float',
        ];
    }
}
