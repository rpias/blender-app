<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: usuario_tipologia_serie
 *
 * @property mixed $id
 * @property mixed $id_usuario
 * @property mixed $id_tipologia
 * @property mixed $id_serie
 */
class UsuarioTipologiaSerie extends Model
{
    protected $table = 'usuario_tipologia_serie';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'id_usuario' => 'integer',
            'id_tipologia' => 'integer',
            'id_serie' => 'integer',
        ];
    }
}
