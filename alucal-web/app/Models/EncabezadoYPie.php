<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: encabezado_y_pie
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $ruta_encabezado
 * @property mixed $ciudad
 * @property mixed $pie
 */
class EncabezadoYPie extends Model
{
    protected $table = 'encabezado_y_pie';

    protected $guarded = [];

    public $timestamps = false;
}
