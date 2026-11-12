<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: tipo_vidrio
 *
 * @property mixed $id
 * @property mixed $tipo
 * @property mixed $abreviado
 * @property mixed $puedeSerBorrado
 */
class TipoVidrio extends Model
{
    protected $table = 'tipo_vidrio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'puedeSerBorrado' => 'integer',
        ];
    }
}
