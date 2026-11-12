<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: margen_operativo_vidrio
 *
 * @property mixed $idVidrio
 * @property mixed $margenOperativo
 */
class MargenOperativoVidrio extends Model
{
    protected $table = 'margen_operativo_vidrio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'margenOperativo' => 'float',
        ];
    }
}
