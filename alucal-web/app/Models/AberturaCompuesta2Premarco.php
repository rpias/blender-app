<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: abertura_compuesta_2_premarco
 *
 * @property mixed $id
 * @property mixed $numPerfil
 * @property mixed $largo
 * @property mixed $cantidad
 * @property mixed $corte
 * @property mixed $orientacion
 * @property mixed $idAberturaCompuesta
 */
class AberturaCompuesta2Premarco extends Model
{
    protected $table = 'abertura_compuesta_2_premarco';

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
