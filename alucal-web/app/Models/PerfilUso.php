<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: perfil_uso
 *
 * @property mixed $idPerfil
 * @property mixed $idUso
 */
class PerfilUso extends Model
{
    protected $table = 'perfil_uso';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idPerfil' => 'integer',
            'idUso' => 'integer',
        ];
    }
}
