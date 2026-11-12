<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_options_mas_otro
 *
 * @property mixed $id
 * @property mixed $option
 * @property mixed $idValorOtro
 */
class ValorParametroParticularOptionsMasOtro extends Model
{
    protected $table = 'valor_parametro_particular_options_mas_otro';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idValorOtro' => 'integer',
        ];
    }
}
