<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: correccion_descuento
 *
 * @property mixed $id
 * @property mixed $idTipologia
 * @property mixed $idSerie
 * @property mixed $correccionAnchoAcristalamiento
 * @property mixed $correccionAltoAcristalamiento
 */
class CorreccionDescuento extends Model
{
    protected $table = 'correccion_descuento';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
            'correccionAnchoAcristalamiento' => 'integer',
            'correccionAltoAcristalamiento' => 'integer',
        ];
    }
}
