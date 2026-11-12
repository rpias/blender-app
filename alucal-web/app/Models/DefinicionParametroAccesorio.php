<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: definicion_parametro_accesorio
 *
 * @property mixed $id
 * @property mixed $idDefinicionParametro
 * @property mixed $esObligatorio
 */
class DefinicionParametroAccesorio extends Model
{
    protected $table = 'definicion_parametro_accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'esObligatorio' => 'integer',
        ];
    }
}
