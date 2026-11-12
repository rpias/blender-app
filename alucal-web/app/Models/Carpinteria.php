<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: carpinteria
 *
 * @property mixed $id
 * @property mixed $nombre
 */
class Carpinteria extends Model
{
    protected $table = 'carpinteria';

    protected $guarded = [];

    public $timestamps = false;
}
