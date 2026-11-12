<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_tipo_corrediza
 *
 * @property mixed $id
 * @property mixed $tipoCorrediza
 * @property mixed $mosquitero
 * @property mixed $encuentroATope
 */
class ValorParametroParticularTipoCorrediza extends Model
{
    protected $table = 'valor_parametro_particular_tipo_corrediza';

    protected $guarded = [];

    public $timestamps = false;
}
