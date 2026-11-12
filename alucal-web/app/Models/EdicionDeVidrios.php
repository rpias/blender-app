<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: edicion_de_vidrios
 *
 * @property mixed $id
 * @property mixed $idAberturaSimple
 * @property mixed $tipoEdicion
 * @property mixed $timestamp
 * @property mixed $vidrio
 * @property mixed $descripcion
 * @property mixed $ancho
 * @property mixed $alto
 * @property mixed $cantidad
 */
class EdicionDeVidrios extends Model
{
    protected $table = 'edicion_de_vidrios';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAberturaSimple' => 'integer',
            'timestamp' => 'date',
            'ancho' => 'integer',
            'alto' => 'integer',
            'cantidad' => 'integer',
        ];
    }
}
