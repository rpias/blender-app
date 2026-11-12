<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: costo_uniblock_mix_armado
 *
 * @property mixed $enumCajon
 * @property mixed $enumCortina
 * @property mixed $costoMinimo
 * @property mixed $costoMetroCuadrado
 * @property mixed $costoLargoCajon
 * @property mixed $costo2doComando
 */
class CostoUniblockMixArmado extends Model
{
    protected $table = 'costo_uniblock_mix_armado';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'costoMinimo' => 'float',
            'costoMetroCuadrado' => 'float',
            'costoLargoCajon' => 'float',
            'costo2doComando' => 'float',
        ];
    }
}
