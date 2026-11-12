<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: conector_marco
 *
 * @property mixed $codigoConector
 * @property mixed $codigoMarco
 */
class ConectorMarco extends Model
{
    protected $table = 'conector_marco';

    protected $guarded = [];

    public $timestamps = false;
}
