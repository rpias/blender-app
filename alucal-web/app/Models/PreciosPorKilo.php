<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: precios_por_kilo
 *
 * @property mixed $id
 * @property mixed $terminacion
 * @property mixed $rpt
 * @property mixed $valor
 */
class PreciosPorKilo extends Model
{
    protected $table = 'precios_por_kilo';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'rpt' => 'integer',
            'valor' => 'float',
        ];
    }
}
