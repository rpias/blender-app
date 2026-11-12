<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: accesorio_sustituto
 *
 * @property mixed $idAccesorio
 * @property mixed $idSustituto
 */
class AccesorioSustituto extends Model
{
    protected $table = 'accesorio_sustituto';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAccesorio' => 'integer',
            'idSustituto' => 'integer',
        ];
    }
}
