<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: edicion_de_accesorios
 *
 * @property mixed $id
 * @property mixed $idAberturaSimple
 * @property mixed $tipoEdicion
 * @property mixed $timestamp
 * @property mixed $idAccesorio
 * @property mixed $parte
 * @property mixed $cantidad
 */
class EdicionDeAccesorios extends Model
{
    protected $table = 'edicion_de_accesorios';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAberturaSimple' => 'integer',
            'timestamp' => 'date',
            'idAccesorio' => 'integer',
            'cantidad' => 'float',
        ];
    }
}
