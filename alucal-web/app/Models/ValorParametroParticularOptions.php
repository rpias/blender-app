<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_options
 *
 * @property mixed $id
 * @property mixed $option
 */
class ValorParametroParticularOptions extends Model
{
    protected $table = 'valor_parametro_particular_options';

    protected $guarded = [];

    public $timestamps = false;
}
