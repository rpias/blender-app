<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: codigo_alternativo_accesorio
 *
 * @property mixed $idAccesorio
 * @property mixed $idProveedorAccesorios
 * @property mixed $codigoAlternativo
 */
class CodigoAlternativoAccesorio extends Model
{
    protected $table = 'codigo_alternativo_accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idAccesorio' => 'integer',
            'idProveedorAccesorios' => 'integer',
        ];
    }
}
