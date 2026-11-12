<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: anexo
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $nombreArchivo
 * @property mixed $palabrasClave
 */
class Anexo extends Model
{
    protected $table = 'anexo';

    protected $guarded = [];

    public $timestamps = false;
}
