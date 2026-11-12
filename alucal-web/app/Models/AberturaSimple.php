<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla: abertura_simple
 *
 * @property mixed $id
 * @property mixed $cantidad
 * @property mixed $ancho
 * @property mixed $alto
 * @property mixed $idTipologia
 * @property mixed $idSerie
 * @property mixed $terminacion
 * @property mixed $numero
 * @property mixed $colorHerrajes
 * @property mixed $colorPlasticos
 * @property mixed $ubicacion
 * @property mixed $tipo
 * @property mixed $idElementoObra
 * @property mixed $idRellenoVano
 */
class AberturaSimple extends Model
{
    protected $table = 'abertura_simple';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'ancho' => 'float',
            'alto' => 'float',
            'idTipologia' => 'integer',
            'idSerie' => 'integer',
            'numero' => 'integer',
            'idElementoObra' => 'integer',
            'idRellenoVano' => 'integer',
            'parametros' => 'array',
            'lineas' => 'array',
            'precioUnitario' => 'integer',
            'calculadoEn' => 'datetime',
        ];
    }

    public function tipologia(): BelongsTo
    {
        return $this->belongsTo(Tipologia::class, 'idTipologia');
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class, 'idSerie');
    }

    public function elemento(): BelongsTo
    {
        return $this->belongsTo(ElementoObra::class, 'idElementoObra');
    }

    /** @return HasMany<CantBarras, $this> */
    public function barras(): HasMany
    {
        return $this->hasMany(CantBarras::class, 'idEstructura');
    }

    /** @return HasMany<CantAccesorios, $this> */
    public function accesorios(): HasMany
    {
        return $this->hasMany(CantAccesorios::class, 'idEstructura');
    }

    /** @return HasMany<CantVidrios, $this> */
    public function vidrios(): HasMany
    {
        return $this->hasMany(CantVidrios::class, 'idEstructura');
    }
}
