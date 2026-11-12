<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: accesorio_perfil
 *
 * @property mixed $idAccesorio
 * @property mixed $idPerfil
 * @property mixed $activo
 */
class AccesorioPerfil extends Model
{
    protected $table = 'accesorio_perfil';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAccesorio' => 'integer',
            'idPerfil' => 'integer',
            'activo' => 'integer',
        ];
    }
}
