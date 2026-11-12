<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: propiedades
 *
 * @property mixed $clave
 * @property mixed $valor
 */
class Propiedades extends Model
{
    protected $table = 'propiedades';

    protected $guarded = [];

    public $timestamps = false;
}
