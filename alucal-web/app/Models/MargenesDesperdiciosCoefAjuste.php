<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: margenes_desperdicios_coef_ajuste
 *
 * @property mixed $idTipologia
 * @property mixed $idSerie
 * @property mixed $MargenMH
 * @property mixed $MargenMHFabrica
 * @property mixed $MargenMosq
 * @property mixed $MargenMosqFabrica
 * @property mixed $DespMH
 * @property mixed $DespMHFabrica
 * @property mixed $DespMosq
 * @property mixed $DespMosqFabrica
 * @property mixed $CoefAjusteObraParticular
 */
class MargenesDesperdiciosCoefAjuste extends Model
{
    protected $table = 'margenes_desperdicios_coef_ajuste';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
            'MargenMH' => 'float',
            'MargenMHFabrica' => 'float',
            'MargenMosq' => 'float',
            'MargenMosqFabrica' => 'float',
            'DespMH' => 'float',
            'DespMHFabrica' => 'float',
            'DespMosq' => 'float',
            'DespMosqFabrica' => 'float',
            'CoefAjusteObraParticular' => 'float',
        ];
    }
}
