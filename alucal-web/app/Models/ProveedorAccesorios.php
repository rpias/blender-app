<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: proveedor_accesorios
 *
 * @property mixed $id
 * @property mixed $nombre
 */
class ProveedorAccesorios extends Model
{
    protected $table = 'proveedor_accesorios';

    protected $guarded = [];

    public $timestamps = false;
}
