<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: coeficientes_de_calculo_de_precio
 *
 * @property mixed $clave
 * @property mixed $valor
 */
class CoeficientesDeCalculoDePrecio extends Model
{
    protected $table = 'coeficientes_de_calculo_de_precio';

    protected $guarded = [];

    public $timestamps = false;
}
