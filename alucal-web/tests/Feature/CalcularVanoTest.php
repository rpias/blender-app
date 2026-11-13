<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\Perfil;
use App\Models\PreciosPorKilo;
use App\Models\VanoDivision;
use App\Services\Aberturas\CalcularVano;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalcularVanoTest extends TestCase
{
    use RefreshDatabase;

    private function obra(): Obra
    {
        return Obra::query()->create([
            'numero' => 'V-'.uniqid(), 'nombre' => 'Vano', 'fechaCreacion' => now()->toDateString(),
        ]);
    }

    private function elemento(Obra $obra, string $tipo, int $numero): ElementoObra
    {
        return ElementoObra::query()->create([
            'tipoElemento' => $tipo, 'idObra' => $obra->id, 'numero' => $numero,
        ]);
    }

    private function aberturaConPrecio(ElementoObra $elemento, int $precio, int $cantidad = 1): AberturaSimple
    {
        return AberturaSimple::query()->create([
            'cantidad' => $cantidad, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 6, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'MARCO_FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => $precio,
        ]);
    }

    public function test_suma_hijos_y_conectores(): void
    {
        Perfil::query()->create(['Numero' => 'ADR 5000', 'Peso' => 1.0, 'Descripcion' => 'CONECTOR']);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 10]);

        $obra = $this->obra();
        $vano = $this->elemento($obra, 'ABERTURA_COMPUESTA', 1);

        $fijo = $this->elemento($obra, 'ABERTURA_SIMPLE', 2);
        $this->aberturaConPrecio($fijo, 100);

        $corrediza = $this->elemento($obra, 'ABERTURA_SIMPLE', 3);
        $this->aberturaConPrecio($corrediza, 200, 2);

        $fijo->update(['idElementoVano' => $vano->id]);
        $corrediza->update(['idElementoVano' => $vano->id]);

        VanoDivision::query()->create([
            'idElementoVano' => $vano->id, 'orientacion' => 'VERTICAL', 'posicionMM' => 2000,
            'codigoConector' => 'ADR 5000', 'terminacion' => 'PINTADO_BLANCO',
        ]);

        $detalle = app(CalcularVano::class)->detalle($vano->fresh());

        // Hijos: 100 + 200×2 = 500. Conector: 2 m × 1 kg/m × 10 × 1,08 × 1,8 ≈ 39.
        $this->assertCount(2, $detalle['hijos']);
        $this->assertCount(1, $detalle['conectores']);
        $this->assertEqualsWithDelta(39, $detalle['conectores'][0]['precio'], 1);
        $this->assertEqualsWithDelta(539, $detalle['total'], 1);
    }

    public function test_sin_conectores_el_total_es_la_suma_de_hijos(): void
    {
        Perfil::query()->create(['Numero' => 'ADR 5001', 'Peso' => 0.5, 'Descripcion' => 'CONECTOR']);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 10]);

        $obra = $this->obra();
        $vano = $this->elemento($obra, 'ABERTURA_COMPUESTA', 1);

        $hijo = $this->elemento($obra, 'ABERTURA_SIMPLE', 2);
        $this->aberturaConPrecio($hijo, 100);
        $hijo->update(['idElementoVano' => $vano->id]);

        $detalle = app(CalcularVano::class)->detalle($vano->fresh());

        $this->assertEqualsWithDelta(100, $detalle['total'], 1);
        $this->assertSame([], $detalle['conectores']);
    }
}
