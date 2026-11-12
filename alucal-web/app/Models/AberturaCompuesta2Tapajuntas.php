<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: abertura_compuesta_2_tapajuntas
 *
 * @property mixed $id
 * @property mixed $numPerfil
 * @property mixed $largo
 * @property mixed $cantidad
 * @property mixed $terminacion
 * @property mixed $corte
 * @property mixed $orientacion
 * @property mixed $idAberturaCompuesta
 */
class AberturaCompuesta2Tapajuntas extends Model
{
    protected $table = 'abertura_compuesta_2_tapajuntas';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'largo' => 'integer',
            'cantidad' => 'integer',
            'idAberturaCompuesta' => 'integer',
        ];
    }
}
