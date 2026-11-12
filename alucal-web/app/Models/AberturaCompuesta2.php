<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: abertura_compuesta_2
 *
 * @property mixed $id
 * @property mixed $tipo
 * @property mixed $numero
 * @property mixed $cantidad
 * @property mixed $ubicacion
 * @property mixed $idHijo
 * @property mixed $idElementoObra
 */
class AberturaCompuesta2 extends Model
{
    protected $table = 'abertura_compuesta_2';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'cantidad' => 'integer',
            'idHijo' => 'integer',
            'idElementoObra' => 'integer',
        ];
    }
}
