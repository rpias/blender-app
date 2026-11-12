<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: lugar_de_uso_accesorio
 *
 * @property mixed $idAccesorio
 * @property mixed $tipologia
 * @property mixed $serie
 * @property mixed $parte
 * @property mixed $idTipologia
 * @property mixed $idSerie
 */
class LugarDeUsoAccesorio extends Model
{
    protected $table = 'lugar_de_uso_accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAccesorio' => 'integer',
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
        ];
    }
}
