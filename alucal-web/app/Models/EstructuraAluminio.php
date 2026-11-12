<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: estructura_aluminio
 *
 * @property mixed $id
 * @property mixed $numero
 * @property mixed $ancho
 * @property mixed $alto
 * @property mixed $descripcion
 * @property mixed $cantidad
 * @property mixed $tipo
 * @property mixed $ubicacion
 * @property mixed $dibujo
 * @property mixed $idElementoObra
 */
class EstructuraAluminio extends Model
{
    protected $table = 'estructura_aluminio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'ancho' => 'integer',
            'alto' => 'integer',
            'cantidad' => 'integer',
            'idElementoObra' => 'integer',
        ];
    }
}
