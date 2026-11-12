<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: lugar_de_uso_perfil
 *
 * @property mixed $idPerfil
 * @property mixed $tipologia
 * @property mixed $serie
 * @property mixed $parte
 * @property mixed $idTipologia
 * @property mixed $idSerie
 * @property mixed $cargadoManual
 */
class LugarDeUsoPerfil extends Model
{
    protected $table = 'lugar_de_uso_perfil';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idPerfil' => 'integer',
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
            'cargadoManual' => 'integer',
        ];
    }
}
