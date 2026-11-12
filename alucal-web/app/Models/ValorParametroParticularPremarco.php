<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_premarco
 *
 * @property mixed $id
 * @property mixed $llevaPremarco
 * @property mixed $llevaTapajuntas
 * @property mixed $tipoPremarco
 * @property mixed $tipoTapajuntas
 * @property mixed $codPremarco
 * @property mixed $codTapajuntas
 * @property mixed $luzSupEnMM
 * @property mixed $luzDerEnMM
 * @property mixed $luzInfEnMM
 * @property mixed $luzIzqEnMM
 */
class ValorParametroParticularPremarco extends Model
{
    protected $table = 'valor_parametro_particular_premarco';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'llevaPremarco' => 'integer',
            'llevaTapajuntas' => 'integer',
            'luzSupEnMM' => 'integer',
            'luzDerEnMM' => 'integer',
            'luzInfEnMM' => 'integer',
            'luzIzqEnMM' => 'integer',
        ];
    }
}
