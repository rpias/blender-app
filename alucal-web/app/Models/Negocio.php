<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: negocio
 *
 * @property mixed $id
 * @property mixed $fechaCreado
 * @property mixed $vivo
 * @property mixed $numero
 * @property mixed $titulo
 * @property mixed $tieneCarpeta
 * @property mixed $montoUSD
 * @property mixed $gestor
 * @property mixed $fechaProximaAccion
 * @property mixed $estado
 * @property mixed $resultadoUSD
 */
class Negocio extends Model
{
    protected $table = 'negocio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fechaCreado' => 'date',
            'vivo' => 'integer',
            'numero' => 'integer',
            'tieneCarpeta' => 'integer',
            'montoUSD' => 'integer',
            'fechaProximaAccion' => 'date',
            'resultadoUSD' => 'integer',
        ];
    }
}
