<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: links_serie
 *
 * @property mixed $idSerie
 * @property mixed $linkInformacionGeneral
 * @property mixed $linkCatalogoTecnico
 */
class LinksSerie extends Model
{
    protected $table = 'links_serie';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idSerie' => 'integer',
        ];
    }
}
