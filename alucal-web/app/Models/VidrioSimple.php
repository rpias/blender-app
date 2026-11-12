<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: vidrio_simple
 *
 * @property mixed $idVidrioSinCamara
 * @property mixed $espesorEnMM
 */
class VidrioSimple extends Model
{
    protected $table = 'vidrio_simple';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'espesorEnMM' => 'integer',
        ];
    }
}
