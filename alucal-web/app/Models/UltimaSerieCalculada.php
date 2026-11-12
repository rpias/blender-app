<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: ultima_serie_calculada
 *
 * @property mixed $Tipologia
 * @property mixed $Serie
 */
class UltimaSerieCalculada extends Model
{
    protected $table = 'ultima_serie_calculada';

    protected $guarded = [];

    public $timestamps = false;
}
