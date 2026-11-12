<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: recordatorio
 *
 * @property mixed $id
 * @property mixed $fecha
 * @property mixed $texto
 */
class Recordatorio extends Model
{
    protected $table = 'recordatorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
