<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_accesorio
 *
 * @property mixed $id
 * @property mixed $codigoAccesorio
 * @property mixed $accesorioFueSeleccionado
 */
class ValorParametroParticularAccesorio extends Model
{
    protected $table = 'valor_parametro_particular_accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'accesorioFueSeleccionado' => 'integer',
        ];
    }
}
