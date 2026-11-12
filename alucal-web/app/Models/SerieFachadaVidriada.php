<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: serie_fachada_vidriada
 *
 * @property mixed $id
 * @property mixed $nombre
 */
class SerieFachadaVidriada extends Model
{
    protected $table = 'serie_fachada_vidriada';

    protected $guarded = [];

    public $timestamps = false;
}
