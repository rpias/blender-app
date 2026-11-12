<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: revestimiento
 *
 * @property mixed $idVidrioSinCamara
 * @property mixed $espesorEnMM
 */
class Revestimiento extends Model
{
    protected $table = 'revestimiento';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'espesorEnMM' => 'integer',
        ];
    }
}
