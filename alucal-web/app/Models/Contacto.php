<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: contacto
 *
 * @property mixed $id
 * @property mixed $idCliente
 * @property mixed $nombre
 * @property mixed $celular
 * @property mixed $email
 */
class Contacto extends Model
{
    protected $table = 'contacto';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idCliente' => 'integer',
        ];
    }
}
