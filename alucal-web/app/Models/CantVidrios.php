<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: cant_vidrios
 *
 * @property mixed $id
 * @property mixed $idEstructura
 * @property mixed $cantidad
 * @property mixed $anchoEnMetros
 * @property mixed $altoEnMetros
 * @property mixed $idVidrioSinCamara
 * @property mixed $idVidrioExterior
 * @property mixed $espesorCamaraEnMM
 * @property mixed $idVidrioInterior
 * @property mixed $descripcion
 */
class CantVidrios extends Model
{
    protected $table = 'cant_vidrios';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idEstructura' => 'integer',
            'cantidad' => 'integer',
            'anchoEnMetros' => 'float',
            'altoEnMetros' => 'float',
            'idVidrioSinCamara' => 'integer',
            'idVidrioExterior' => 'integer',
            'espesorCamaraEnMM' => 'integer',
            'idVidrioInterior' => 'integer',
        ];
    }
}
