<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: serie_fachada_vidriada_accesorios
 *
 * @property mixed $idSerieFachadaVidriada
 * @property mixed $idAccesorio
 */
class SerieFachadaVidriadaAccesorios extends Model
{
    protected $table = 'serie_fachada_vidriada_accesorios';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idSerieFachadaVidriada' => 'integer',
            'idAccesorio' => 'integer',
        ];
    }
}
