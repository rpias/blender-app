<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: vidrio_laminado
 *
 * @property mixed $idVidrioSinCamara
 * @property mixed $espesorPorPiezaEnMM
 */
class VidrioLaminado extends Model
{
    protected $table = 'vidrio_laminado';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'espesorPorPiezaEnMM' => 'integer',
        ];
    }
}
