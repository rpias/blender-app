<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Tabla: elemento_obra
 *
 * @property mixed $id
 * @property mixed $tipoElemento
 * @property mixed $idObra
 * @property mixed $numero
 */
class ElementoObra extends Model
{
    protected $table = 'elemento_obra';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idObra' => 'integer',
            'numero' => 'integer',
        ];
    }

    /** @return BelongsTo<Obra, $this> */
    /**
     * Divisiones del vano cuando este elemento es una abertura compuesta (spec 0009).
     */
    public function vanoDivisiones(): HasMany
    {
        return $this->hasMany(VanoDivision::class, 'idElementoVano');
    }

    /**
     * Aberturas agrupadas en este vano.
     */
    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'idElementoVano');
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class, 'idObra');
    }

    /** @return HasOne<AberturaSimple, $this> */
    public function aberturaSimple(): HasOne
    {
        return $this->hasOne(AberturaSimple::class, 'idElementoObra');
    }

    /** @return HasOne<AberturaCompuesta2, $this> */
    public function aberturaCompuesta(): HasOne
    {
        return $this->hasOne(AberturaCompuesta2::class, 'idElementoObra');
    }

    /** @return HasOne<EstructuraAluminio, $this> */
    public function estructura(): HasOne
    {
        return $this->hasOne(EstructuraAluminio::class, 'idElementoObra');
    }
}
