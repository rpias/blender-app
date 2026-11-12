<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_cierres_corrediza
 *
 * @property mixed $id
 * @property mixed $personalizarCierres
 * @property mixed $cierres
 */
class ValorParametroParticularCierresCorrediza extends Model
{
    protected $table = 'valor_parametro_particular_cierres_corrediza';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'personalizarCierres' => 'integer',
        ];
    }
}
