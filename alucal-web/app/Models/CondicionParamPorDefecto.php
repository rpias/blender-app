<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: condicion_param_por_defecto
 *
 * @property mixed $id
 * @property mixed $idParamPorDefecto
 * @property mixed $datoAsociado
 * @property mixed $rangoDesde
 * @property mixed $rangoHasta
 */
class CondicionParamPorDefecto extends Model
{
    protected $table = 'condicion_param_por_defecto';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idParamPorDefecto' => 'integer',
            'rangoDesde' => 'integer',
            'rangoHasta' => 'integer',
        ];
    }
}
