<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: perfil_serie
 *
 * @property mixed $id
 * @property mixed $idPerfil
 * @property mixed $idSerie
 */
class PerfilSerie extends Model
{
    protected $table = 'perfil_serie';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idPerfil' => 'integer',
            'idSerie' => 'integer',
        ];
    }
}
