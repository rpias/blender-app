<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: serie_fachada_vidriada_perfil
 *
 * @property mixed $idSerieFachadaVidriada
 * @property mixed $idPerfil
 */
class SerieFachadaVidriadaPerfil extends Model
{
    protected $table = 'serie_fachada_vidriada_perfil';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idSerieFachadaVidriada' => 'integer',
            'idPerfil' => 'integer',
        ];
    }
}
