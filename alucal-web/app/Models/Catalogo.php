<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: catalogo
 *
 * @property mixed $idSerie
 * @property mixed $ruta
 */
class Catalogo extends Model
{
    protected $table = 'catalogo';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idSerie' => 'integer',
        ];
    }
}
