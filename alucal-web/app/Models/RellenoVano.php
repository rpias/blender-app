<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: relleno_vano
 *
 * @property mixed $id
 * @property mixed $tipoRellenoVano
 */
class RellenoVano extends Model
{
    protected $table = 'relleno_vano';

    protected $guarded = [];

    public $timestamps = false;
}
