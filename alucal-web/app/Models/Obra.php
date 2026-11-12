<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Tabla: obra
 *
 * El Java guarda algunas fechas como epoch en milisegundos dentro de columnas
 * DATE; los accesores las normalizan.
 *
 * @property mixed $id
 * @property mixed $idCarpinteria
 * @property mixed $numero
 * @property mixed $nombre
 * @property mixed $creador
 * @property mixed $fechaCreacion
 * @property mixed $fechaUltModificacion
 * @property mixed $arquitecto
 * @property mixed $cliente
 * @property mixed $ubicacion
 * @property mixed $estadoSeguimiento
 * @property mixed $proximaAccionFecha
 * @property mixed $proximaAccionDetalle
 */
class Obra extends Model
{
    protected $table = 'obra';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'idCarpinteria' => 'integer',
        ];
    }

    /** @return HasMany<ElementoObra, $this> */
    public function elementos(): HasMany
    {
        return $this->hasMany(ElementoObra::class, 'idObra');
    }

    /**
     * Elementos que hoy se pueden presupuestar (abertura simple con cálculo).
     * Los compuestos y los elementos huérfanos del Java quedan afuera.
     *
     * @return Collection<int, ElementoObra>
     */
    public function elementosPresupuestables(): Collection
    {
        return $this->elementos
            ->filter(fn (ElementoObra $elemento) => $elemento->aberturaSimple !== null)
            ->values();
    }

    public function clienteRegistrado(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente', 'nombre');
    }

    protected function fechaCreacion(): Attribute
    {
        return Attribute::make(get: fn ($valor) => self::comoFecha($valor));
    }

    protected function fechaUltModificacion(): Attribute
    {
        return Attribute::make(get: fn ($valor) => self::comoFecha($valor));
    }

    protected function proximaAccionFecha(): Attribute
    {
        return Attribute::make(get: fn ($valor) => self::comoFecha($valor));
    }

    /**
     * Acepta epoch en milisegundos (Java), epoch en segundos o fecha ISO.
     */
    public static function comoFecha(mixed $valor): ?CarbonImmutable
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            $numero = (float) $valor;

            return $numero > 100000000000
                ? CarbonImmutable::createFromTimestampMs((int) $numero)
                : CarbonImmutable::createFromTimestamp((int) $numero);
        }

        try {
            return CarbonImmutable::parse((string) $valor);
        } catch (\Throwable) {
            return null;
        }
    }
}
