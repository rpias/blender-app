<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: valor_parametro_particular_acristalamiento
 *
 * @property mixed $id
 * @property mixed $tipoAcristalamiento
 * @property mixed $materialAcristalamientoEntero
 * @property mixed $materialAcristalamientoSuperior
 * @property mixed $materialAcristalamientoInferior
 * @property mixed $cristalEntero
 * @property mixed $cristalSuperior
 * @property mixed $cristalInferior
 * @property mixed $divisor
 * @property mixed $tablillaEnteraVertical
 * @property mixed $tablillaSuperiorVertical
 * @property mixed $tablillaInferiorVertical
 * @property mixed $dimensionesColonialEntero
 * @property mixed $dimensionesColonialSuperior
 * @property mixed $dimensionesColonialInferior
 * @property mixed $tipoAcristalamiento2
 * @property mixed $materialAcristalamientoEntero2
 * @property mixed $materialAcristalamientoSuperior2
 * @property mixed $materialAcristalamientoInferior2
 * @property mixed $cristalEntero2
 * @property mixed $cristalSuperior2
 * @property mixed $cristalInferior2
 * @property mixed $divisor2
 * @property mixed $tablillaEnteraVertical2
 * @property mixed $tablillaSuperiorVertical2
 * @property mixed $tablillaInferiorVertical2
 * @property mixed $dimensionesColonialEntero2
 * @property mixed $dimensionesColonialSuperior2
 * @property mixed $dimensionesColonialInferior2
 */
class ValorParametroParticularAcristalamiento extends Model
{
    protected $table = 'valor_parametro_particular_acristalamiento';

    protected $guarded = [];

    public $timestamps = false;
}
