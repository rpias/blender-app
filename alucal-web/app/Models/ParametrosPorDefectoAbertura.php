<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: parametros_por_defecto_abertura
 *
 * @property mixed $id
 * @property mixed $idTipologia
 * @property mixed $idSerie
 */
class ParametrosPorDefectoAbertura extends Model
{
    protected $table = 'parametros_por_defecto_abertura';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
        ];
    }
}
