<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: edicion_de_perfiles
 *
 * @property mixed $id
 * @property mixed $idAberturaSimple
 * @property mixed $tipoEdicion
 * @property mixed $timestamp
 * @property mixed $idPerfil
 * @property mixed $cantidad
 * @property mixed $largo
 * @property mixed $descripcion
 * @property mixed $parte
 * @property mixed $corte
 * @property mixed $terminacion
 * @property mixed $orientacion
 */
class EdicionDePerfiles extends Model
{
    protected $table = 'edicion_de_perfiles';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAberturaSimple' => 'integer',
            'timestamp' => 'date',
            'idPerfil' => 'integer',
            'cantidad' => 'integer',
            'largo' => 'integer',
        ];
    }
}
