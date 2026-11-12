<?php

namespace App\Services\Precios;

use App\Models\CoeficientesDeCalculoDePrecio;
use App\Models\MargenesDesperdiciosCoefAjuste;
use App\Models\PrecioColocacion;
use App\Models\PrecioMetroLinealPremarco;
use App\Models\PrecioMetroLinealTapajuntas;
use App\Models\PreciosPorKilo;

/**
 * Coeficientes y tablas de precio cargados una vez por request.
 *
 * Replica el criterio del motor Java MARGEN_OPERATIVO
 * (services/precios/FactoryServiceCalcularPrecio.java).
 */
class Coeficientes
{
    /** @var array<string, float>|null */
    private ?array $coeficientes = null;

    /** @var array<string, array{0: float, 1: float, 2: float, 3: float}> */
    private array $margenes = [];

    /** @var array<string, float> */
    private array $preciosPorKilo = [];

    /** @var array<string, float> */
    private array $precioPremarco = [];

    /** @var array<string, float> */
    private array $precioTapajuntas = [];

    /** @var array<string, array{0: int, 1: int}> */
    private array $colocacion = [];

    public function coeficiente(string $clave, float $defecto = 0.0): float
    {
        if ($this->coeficientes === null) {
            $this->coeficientes = CoeficientesDeCalculoDePrecio::query()
                ->pluck('valor', 'clave')
                ->map(fn ($valor) => (float) $valor)
                ->all();
        }

        return $this->coeficientes[$clave] ?? $defecto;
    }

    /**
     * Coeficientes de margen/desperdicio de una tipología-serie.
     *
     * @return array{margenMH: float, margenMosq: float, despMH: float, despMosq: float, coefAjuste: float}
     */
    public function margenes(int $idTipologia, int $idSerie): array
    {
        $clave = "{$idTipologia}:{$idSerie}";

        if (isset($this->margenes[$clave])) {
            return $this->margenes[$clave];
        }

        $fila = MargenesDesperdiciosCoefAjuste::query()
            ->where('idTipologia', $idTipologia)
            ->where('idSerie', $idSerie)
            ->first();

        // Fallback determinista: si falta la combinación exacta, se toma la
        // primera serie de esa tipología ordenada por idSerie. El original
        // iteraba un HashMap (orden no especificado); acá se fija la regla.
        if ($fila === null) {
            $fila = MargenesDesperdiciosCoefAjuste::query()
                ->where('idTipologia', $idTipologia)
                ->orderBy('idSerie')
                ->first();
        }

        $valores = [
            'margenMH' => (float) ($fila->MargenMH ?? 1.0),
            'margenMosq' => (float) ($fila->MargenMosq ?? 1.0),
            'despMH' => (float) ($fila->DespMH ?? 1.0),
            'despMosq' => (float) ($fila->DespMosq ?? 1.0),
            'coefAjuste' => (float) ($fila->CoefAjusteObraParticular ?? 1.0),
        ];

        return $this->margenes[$clave] = $valores;
    }

    /**
     * Precio por kilo de aluminio según terminación y RPT.
     *
     * Orden de resolución idéntico al original: combinación exacta → mismo
     * acabado con RPT invertido → mismo acabado → primer valor disponible.
     */
    public function precioPorKilo(string $terminacion, bool $rpt): float
    {
        if ($this->preciosPorKilo === []) {
            $this->preciosPorKilo = PreciosPorKilo::query()
                ->get()
                ->mapWithKeys(fn ($fila) => [
                    $fila->terminacion.':'.(int) $fila->rpt => (float) $fila->valor,
                ])
                ->all();
        }

        $claves = [
            $terminacion.':'.(int) $rpt,
            $terminacion.':'.(int) ! $rpt,
        ];

        foreach ($claves as $clave) {
            if (isset($this->preciosPorKilo[$clave])) {
                return $this->preciosPorKilo[$clave];
            }
        }

        foreach ($this->preciosPorKilo as $clave => $valor) {
            if (str_starts_with($clave, $terminacion.':')) {
                return $valor;
            }
        }

        return $this->preciosPorKilo === [] ? 0.0 : reset($this->preciosPorKilo);
    }

    public function precioMetroPremarco(string $codigoPerfil): ?float
    {
        if ($this->precioPremarco === []) {
            $this->precioPremarco = PrecioMetroLinealPremarco::query()
                ->pluck('precio', 'perfil')
                ->map(fn ($valor) => (float) $valor)
                ->all();
        }

        return $this->precioPremarco[$codigoPerfil] ?? null;
    }

    public function precioMetroTapajuntas(string $codigoPerfil): ?float
    {
        if ($this->precioTapajuntas === []) {
            $this->precioTapajuntas = PrecioMetroLinealTapajuntas::query()
                ->pluck('precio', 'perfil')
                ->map(fn ($valor) => (float) $valor)
                ->all();
        }

        return $this->precioTapajuntas[$codigoPerfil] ?? null;
    }

    /**
     * @return array{0: int, 1: int}|null [precioBase, precioPorMetroLineal]
     */
    public function colocacion(string $tipo): ?array
    {
        if ($this->colocacion === []) {
            $this->colocacion = PrecioColocacion::query()
                ->get()
                ->mapWithKeys(fn ($fila) => [
                    $fila->tipoColocacion => [(int) $fila->precioBase, (int) $fila->precioPorMetroLineal],
                ])
                ->all();
        }

        return $this->colocacion[$tipo] ?? null;
    }
}
