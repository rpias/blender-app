<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: accesorio
 *
 * @property mixed $id
 * @property mixed $codigo
 * @property mixed $descripcion
 * @property mixed $precio
 * @property mixed $unidad
 * @property mixed $cantPrecio
 * @property mixed $comentario
 * @property mixed $codigoAlcan
 * @property mixed $codigoTanit
 * @property mixed $codigoSapa
 * @property mixed $idCategoria
 * @property mixed $palabrasClave
 * @property mixed $idMaterial
 * @property mixed $activo
 * @property mixed $perteneceAListadoAccImportados
 * @property mixed $stockMinimo
 * @property mixed $reposicion
 * @property mixed $cantHorasColocacion
 */
class Accesorio extends Model
{
    protected $table = 'accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'cantPrecio' => 'integer',
            'idCategoria' => 'integer',
            'idMaterial' => 'integer',
            'activo' => 'integer',
            'perteneceAListadoAccImportados' => 'integer',
            'stockMinimo' => 'integer',
            'reposicion' => 'integer',
            'cantHorasColocacion' => 'float',
        ];
    }
}
