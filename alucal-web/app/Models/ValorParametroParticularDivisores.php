<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_divisores
 *
 * @property mixed $id
 * @property mixed $cantDivisoresHorizontales
 * @property mixed $divisorHorizontal
 * @property mixed $cantDivisoresVerticales
 * @property mixed $divisorVertical
 */
class ValorParametroParticularDivisores extends Model
{
    protected $table = 'valor_parametro_particular_divisores';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantDivisoresHorizontales' => 'integer',
            'cantDivisoresVerticales' => 'integer',
        ];
    }
}
