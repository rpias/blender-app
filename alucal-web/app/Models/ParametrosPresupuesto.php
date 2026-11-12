<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: parametros_presupuesto
 *
 * @property mixed $clave
 * @property mixed $valor
 */
class ParametrosPresupuesto extends Model
{
    protected $table = 'parametros_presupuesto';

    protected $guarded = [];

    public $timestamps = false;
}
