<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: definicion_parametro_acristalamiento
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $acristalamientoCorr2Mas1
 * @property mixed $opcionVidrioSinCamara
 * @property mixed $opcionDVH
 * @property mixed $opcionTablilla
 * @property mixed $opcionDivisorAlMedio
 * @property mixed $opcionColonial
 * @property mixed $divisores
 */
class DefinicionParametroAcristalamiento extends Model
{
    protected $table = 'definicion_parametro_acristalamiento';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idDefinicionParametro' => 'integer',
            'acristalamientoCorr2Mas1' => 'integer',
            'opcionVidrioSinCamara' => 'integer',
            'opcionDVH' => 'integer',
            'opcionTablilla' => 'integer',
            'opcionDivisorAlMedio' => 'integer',
            'opcionColonial' => 'integer',
        ];
    }
}
