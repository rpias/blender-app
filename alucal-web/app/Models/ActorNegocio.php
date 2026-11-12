<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: actor_negocio
 *
 * @property mixed $id
 * @property mixed $idNegocio
 * @property mixed $nombre
 * @property mixed $celular
 * @property mixed $email
 * @property mixed $posicion
 */
class ActorNegocio extends Model
{
    protected $table = 'actor_negocio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idNegocio' => 'integer',
        ];
    }
}
