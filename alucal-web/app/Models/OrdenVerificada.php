<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: orden_verificada
 *
 * @property mixed $id
 * @property mixed $idTipologia
 * @property mixed $idSerie
 * @property mixed $perfilesVerificados
 * @property mixed $accesoriosVerificados
 * @property mixed $vidriosVerificados
 */
class OrdenVerificada extends Model
{
    protected $table = 'orden_verificada';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
            'perfilesVerificados' => 'integer',
            'accesoriosVerificados' => 'integer',
            'vidriosVerificados' => 'integer',
        ];
    }
}
