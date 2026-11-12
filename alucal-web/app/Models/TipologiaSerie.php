<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: tipologia_serie
 *
 * @property mixed $idTipologia
 * @property mixed $idSerie
 * @property mixed $usada
 * @property mixed $maxEspesorVidrioEnMM
 * @property mixed $perfilesVerificados
 * @property mixed $accesoriosVerificados
 * @property mixed $vidriosVerificados
 * @property mixed $cantHorasMinimo
 * @property mixed $cantHorasChica
 * @property mixed $cantHorasGrande
 * @property mixed $anchoChicaEnMM
 * @property mixed $altoChicaEnMM
 * @property mixed $anchoGrandeEnMM
 * @property mixed $altoGrandeEnMM
 */
class TipologiaSerie extends Model
{
    protected $table = 'tipologia_serie';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
            'usada' => 'integer',
            'maxEspesorVidrioEnMM' => 'integer',
            'perfilesVerificados' => 'integer',
            'accesoriosVerificados' => 'integer',
            'vidriosVerificados' => 'integer',
            'cantHorasMinimo' => 'float',
            'cantHorasChica' => 'float',
            'cantHorasGrande' => 'float',
            'anchoChicaEnMM' => 'integer',
            'altoChicaEnMM' => 'integer',
            'anchoGrandeEnMM' => 'integer',
            'altoGrandeEnMM' => 'integer',
        ];
    }
}
