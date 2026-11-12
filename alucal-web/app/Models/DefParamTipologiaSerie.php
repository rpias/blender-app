<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: def_param_tipologia_serie
 *
 * @property mixed $idDefParam
 * @property mixed $idTipologia
 * @property mixed $idSerie
 */
class DefParamTipologiaSerie extends Model
{
    protected $table = 'def_param_tipologia_serie';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefParam' => 'integer',
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
        ];
    }
}
