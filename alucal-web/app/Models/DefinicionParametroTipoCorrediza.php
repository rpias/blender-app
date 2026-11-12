<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: definicion_parametro_tipo_corrediza
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $maxCantHojas
 * @property mixed $tieneMarco2Guias
 * @property mixed $tieneMarco3Guias
 * @property mixed $tieneMarco4Guias
 * @property mixed $opcionMosquitero
 * @property mixed $mosquiteros
 * @property mixed $encuentrosATope
 * @property mixed $mosqDebeUtilizarGuiaDeHoja
 */
class DefinicionParametroTipoCorrediza extends Model
{
    protected $table = 'definicion_parametro_tipo_corrediza';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'maxCantHojas' => 'integer',
            'tieneMarco2Guias' => 'integer',
            'tieneMarco3Guias' => 'integer',
            'tieneMarco4Guias' => 'integer',
            'opcionMosquitero' => 'integer',
            'mosqDebeUtilizarGuiaDeHoja' => 'integer',
        ];
    }
}
