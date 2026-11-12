<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: correccion_descuento_perfil
 *
 * @property mixed $idCorreccionDescuento
 * @property mixed $perfil
 * @property mixed $orientacion
 * @property mixed $correccion
 */
class CorreccionDescuentoPerfil extends Model
{
    protected $table = 'correccion_descuento_perfil';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idCorreccionDescuento' => 'integer',
            'correccion' => 'integer',
        ];
    }
}
