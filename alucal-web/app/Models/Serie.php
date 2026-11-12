<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: serie
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $abreviacion
 * @property mixed $rpt
 * @property mixed $activa
 */
class Serie extends Model
{
    protected $table = 'serie';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'rpt' => 'integer',
            'activa' => 'integer',
        ];
    }
}
