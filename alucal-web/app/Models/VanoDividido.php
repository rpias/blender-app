<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: vano_dividido
 *
 * @property mixed $id
 * @property mixed $ancho
 * @property mixed $alto
 * @property mixed $llevaConector
 * @property mixed $codigoConector
 * @property mixed $terminacionConector
 * @property mixed $esDivisionHorizontal
 * @property mixed $posicionDivision
 * @property mixed $idHijo1
 * @property mixed $idHijo2
 * @property mixed $idRellenoVano
 */
class VanoDividido extends Model
{
    protected $table = 'vano_dividido';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'ancho' => 'integer',
            'alto' => 'integer',
            'llevaConector' => 'integer',
            'esDivisionHorizontal' => 'integer',
            'posicionDivision' => 'integer',
            'idHijo1' => 'integer',
            'idHijo2' => 'integer',
            'idRellenoVano' => 'integer',
        ];
    }
}
