<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: cant_accesorios
 *
 * @property mixed $id
 * @property mixed $idEstructura
 * @property mixed $idValorParametroParticular
 * @property mixed $accesorio
 * @property mixed $cantidad
 * @property mixed $parte
 */
class CantAccesorios extends Model
{
    protected $table = 'cant_accesorios';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idEstructura' => 'integer',
            'idValorParametroParticular' => 'integer',
            'cantidad' => 'float',
        ];
    }
}
