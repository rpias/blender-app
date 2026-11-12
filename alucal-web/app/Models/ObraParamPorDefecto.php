<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: obra_param_por_defecto
 *
 * @property mixed $idObra
 * @property mixed $terminacion
 * @property mixed $llevaPremarco
 * @property mixed $codigoPremarco
 * @property mixed $llevaTapajuntas
 * @property mixed $llevaMosquitero
 */
class ObraParamPorDefecto extends Model
{
    protected $table = 'obra_param_por_defecto';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'llevaPremarco' => 'integer',
            'llevaTapajuntas' => 'integer',
            'llevaMosquitero' => 'integer',
        ];
    }
}
