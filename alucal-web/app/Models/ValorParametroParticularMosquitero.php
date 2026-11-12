<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_mosquitero
 *
 * @property mixed $id
 * @property mixed $llevaMosquitero
 * @property mixed $codigoMosquitero
 */
class ValorParametroParticularMosquitero extends Model
{
    protected $table = 'valor_parametro_particular_mosquitero';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'llevaMosquitero' => 'integer',
        ];
    }
}
