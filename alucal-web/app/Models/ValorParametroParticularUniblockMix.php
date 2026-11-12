<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_uniblock_mix
 *
 * @property mixed $id
 * @property mixed $conCortina
 * @property mixed $materialCortina
 * @property mixed $materialCajon
 * @property mixed $altoCajon
 * @property mixed $sonMedidasVano
 * @property mixed $jamba
 */
class ValorParametroParticularUniblockMix extends Model
{
    protected $table = 'valor_parametro_particular_uniblock_mix';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'conCortina' => 'integer',
            'altoCajon' => 'integer',
            'sonMedidasVano' => 'integer',
        ];
    }
}
