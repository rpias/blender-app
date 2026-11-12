<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_perfil
 *
 * @property mixed $id
 * @property mixed $codigoPerfil
 */
class ValorParametroParticularPerfil extends Model
{
    protected $table = 'valor_parametro_particular_perfil';

    protected $guarded = [];

    public $timestamps = false;
}
