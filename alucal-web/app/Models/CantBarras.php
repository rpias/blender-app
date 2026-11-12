<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: cant_barras
 *
 * @property mixed $id
 * @property mixed $idEstructura
 * @property mixed $idValorParametroParticular
 * @property mixed $perfil
 * @property mixed $cantidad
 * @property mixed $largoEnMM
 * @property mixed $descripcion
 * @property mixed $parte
 * @property mixed $corte
 * @property mixed $terminacion
 * @property mixed $orientacion
 */
class CantBarras extends Model
{
    protected $table = 'cant_barras';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idEstructura' => 'integer',
            'idValorParametroParticular' => 'integer',
            'cantidad' => 'integer',
            'largoEnMM' => 'integer',
        ];
    }
}
