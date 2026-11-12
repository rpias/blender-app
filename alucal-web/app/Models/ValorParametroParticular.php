<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular
 *
 * @property mixed $id
 * @property mixed $idAberturaSimple
 * @property mixed $idParamPorDefecto
 * @property mixed $idCorreccionDescuentos
 * @property mixed $idOrdenVerificada
 * @property mixed $idDefParamParticular
 */
class ValorParametroParticular extends Model
{
    protected $table = 'valor_parametro_particular';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAberturaSimple' => 'integer',
            'idParamPorDefecto' => 'integer',
            'idCorreccionDescuentos' => 'integer',
            'idOrdenVerificada' => 'integer',
            'idDefParamParticular' => 'integer',
        ];
    }
}
