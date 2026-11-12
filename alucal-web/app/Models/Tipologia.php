<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: tipologia
 *
 * @property mixed $id
 * @property mixed $nombre
 * @property mixed $abreviacion
 * @property mixed $esMampara
 * @property mixed $puedeLlevarAcristalamiento
 */
class Tipologia extends Model
{
    protected $table = 'tipologia';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'esMampara' => 'integer',
            'puedeLlevarAcristalamiento' => 'integer',
        ];
    }
}
