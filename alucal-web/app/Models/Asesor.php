<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: asesor
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $celular
 * @property mixed $email
 */
class Asesor extends Model
{
    protected $table = 'asesor';

    protected $guarded = [];

    public $timestamps = false;
}
