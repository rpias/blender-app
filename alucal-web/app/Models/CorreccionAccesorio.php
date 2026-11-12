<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: correccion_accesorio
 *
 * @property mixed $idCorreccionDescuento
 * @property mixed $codigoAccesorio
 * @property mixed $tipoCorreccion
 * @property mixed $parte
 * @property mixed $cantidad
 */
class CorreccionAccesorio extends Model
{
    protected $table = 'correccion_accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idCorreccionDescuento' => 'integer',
            'cantidad' => 'float',
        ];
    }
}
