<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: precio_minimo_vidrio
 *
 * @property mixed $idVidrio
 * @property mixed $precioMinimo
 */
class PrecioMinimoVidrio extends Model
{
    protected $table = 'precio_minimo_vidrio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precioMinimo' => 'float',
        ];
    }
}
