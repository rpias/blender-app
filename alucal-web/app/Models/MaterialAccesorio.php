<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: material_accesorio
 *
 * @property mixed $id
 * @property mixed $nombre
 */
class MaterialAccesorio extends Model
{
    protected $table = 'material_accesorio';

    protected $guarded = [];

    public $timestamps = false;
}
