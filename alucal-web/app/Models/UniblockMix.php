<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: uniblock_mix
 *
 * @property mixed $id
 * @property mixed $anchoEnMM
 * @property mixed $altoEnMM
 * @property mixed $cajon
 * @property mixed $cortina
 * @property mixed $idHijo
 * @property mixed $idRellenoVano
 * @property mixed $jamba
 */
class UniblockMix extends Model
{
    protected $table = 'uniblock_mix';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'anchoEnMM' => 'integer',
            'altoEnMM' => 'integer',
            'idHijo' => 'integer',
            'idRellenoVano' => 'integer',
        ];
    }
}
