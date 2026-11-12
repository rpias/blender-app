<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: categoria_accesorio
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $codigoCategoria
 */
class CategoriaAccesorio extends Model
{
    protected $table = 'categoria_accesorio';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }
}
