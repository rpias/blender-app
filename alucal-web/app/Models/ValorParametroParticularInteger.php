<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_integer
 *
 * @property mixed $id
 * @property mixed $valor
 */
class ValorParametroParticularInteger extends Model
{
    protected $table = 'valor_parametro_particular_integer';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'valor' => 'integer',
        ];
    }
}
