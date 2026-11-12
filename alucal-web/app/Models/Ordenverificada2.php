<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: ordenverificada2
 *
 * @property mixed $id
 * @property mixed $vectorDeVerificacion
 * @property mixed $registradoPor
 * @property mixed $idSerie
 * @property mixed $idTipologia
 * @property mixed $fechaVerificacion
 * @property mixed $comentario
 */
class Ordenverificada2 extends Model
{
    protected $table = 'ordenverificada2';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idSerie' => 'integer',
            'idTipologia' => 'integer',
            'fechaVerificacion' => 'date',
        ];
    }
}
