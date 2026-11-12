<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: perfil_datos_cargados
 *
 * @property mixed $idPerfil
 * @property mixed $usos
 * @property mixed $series
 * @property mixed $tubular
 */
class PerfilDatosCargados extends Model
{
    protected $table = 'perfil_datos_cargados';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'usos' => 'integer',
            'series' => 'integer',
            'tubular' => 'integer',
        ];
    }
}
