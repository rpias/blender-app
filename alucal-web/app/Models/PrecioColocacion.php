<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: precio_colocacion
 *
 * @property mixed $tipoColocacion
 * @property mixed $precioBase
 * @property mixed $precioPorMetroLineal
 */
class PrecioColocacion extends Model
{
    protected $table = 'precio_colocacion';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precioBase' => 'integer',
            'precioPorMetroLineal' => 'integer',
        ];
    }
}
