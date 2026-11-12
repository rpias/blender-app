<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: contacto_empresa
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $celular
 * @property mixed $email
 */
class ContactoEmpresa extends Model
{
    protected $table = 'contacto_empresa';

    protected $guarded = [];

    public $timestamps = false;
}
