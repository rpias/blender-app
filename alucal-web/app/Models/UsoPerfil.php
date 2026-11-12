<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: uso_perfil
 *
 * @property mixed $id
 * @property mixed $nombre
 */
class UsoPerfil extends Model
{
    protected $table = 'uso_perfil';

    protected $guarded = [];

    public $timestamps = false;
}
