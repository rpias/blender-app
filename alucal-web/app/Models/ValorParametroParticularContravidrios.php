<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_contravidrios
 *
 * @property mixed $id
 * @property mixed $contravidriosDefinidos
 * @property mixed $contravidrioHorizontal
 * @property mixed $contravidrioVertical
 */
class ValorParametroParticularContravidrios extends Model
{
    protected $table = 'valor_parametro_particular_contravidrios';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'contravidriosDefinidos' => 'integer',
        ];
    }
}
