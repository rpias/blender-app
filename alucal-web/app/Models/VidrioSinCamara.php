<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: vidrio_sin_camara
 *
 * @property mixed $id
 * @property mixed $idTipo
 * @property mixed $precio
 * @property mixed $precioFabrica
 * @property mixed $precioMinimo
 * @property mixed $precioMinimoFabrica
 * @property mixed $margen
 * @property mixed $margenFabrica
 */
class VidrioSinCamara extends Model
{
    protected $table = 'vidrio_sin_camara';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idTipo' => 'integer',
            'precio' => 'float',
            'precioFabrica' => 'float',
            'precioMinimo' => 'float',
            'precioMinimoFabrica' => 'float',
            'margen' => 'float',
            'margenFabrica' => 'float',
        ];
    }
}
