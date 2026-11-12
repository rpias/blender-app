<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: evento_seguimiento_obra
 *
 * @property mixed $id
 * @property mixed $idObra
 * @property mixed $fecha
 * @property mixed $detalle
 */
class EventoSeguimientoObra extends Model
{
    protected $table = 'evento_seguimiento_obra';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idObra' => 'integer',
            'fecha' => 'date',
        ];
    }
}
