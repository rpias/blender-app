<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: empresa
 *
 * @property mixed $numero
 * @property mixed $nombre
 * @property mixed $alias
 * @property mixed $direccion
 * @property mixed $telefono
 */
class Empresa extends Model
{
    protected $table = 'empresa';

    protected $guarded = [];

    public $timestamps = false;
}
