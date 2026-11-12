<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: perfil_confirmado
 *
 * @property mixed $idPerfil
 * @property mixed $color
 */
class PerfilConfirmado extends Model
{
    protected $table = 'perfil_confirmado';

    protected $guarded = [];

    public $timestamps = false;
}
