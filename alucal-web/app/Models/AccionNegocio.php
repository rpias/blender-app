<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: accion_negocio
 *
 * @property mixed $id
 * @property mixed $idNegocio
 * @property mixed $fecha
 * @property mixed $texto
 * @property mixed $firma
 */
class AccionNegocio extends Model
{
    protected $table = 'accion_negocio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idNegocio' => 'integer',
            'fecha' => 'date',
        ];
    }
}
