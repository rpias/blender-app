<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: uso
 *
 * @property mixed $id
 * @property mixed $nombre
 */
class Uso extends Model
{
    protected $table = 'uso';

    protected $guarded = [];

    public $timestamps = false;
}
