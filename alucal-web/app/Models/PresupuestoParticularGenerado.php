<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: presupuesto_particular_generado
 *
 * @property mixed $id
 * @property mixed $tipologia
 * @property mixed $serie
 * @property mixed $medidas
 * @property mixed $fechaHoraGenerado
 * @property mixed $cliente
 * @property mixed $fileName
 */
class PresupuestoParticularGenerado extends Model
{
    protected $table = 'presupuesto_particular_generado';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fechaHoraGenerado' => 'date',
        ];
    }
}
