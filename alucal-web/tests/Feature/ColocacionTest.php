<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\PrecioColocacion;
use App\Models\User;
use App\Services\Precios\CalculadoraColocacion;
use App\Servicios\Exportacion\LineasPresupuesto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColocacionTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);

        PrecioColocacion::query()->create([
            'tipoColocacion' => 'ABERTURA_ATORNILLAR', 'precioBase' => 20, 'precioPorMetroLineal' => 12,
        ]);
        PrecioColocacion::query()->create([
            'tipoColocacion' => 'MAMPARA_2_HOJAS', 'precioBase' => 50, 'precioPorMetroLineal' => 0,
        ]);
    }

    public function test_calcula_base_mas_perimetro(): void
    {
        $resultado = app(CalculadoraColocacion::class)->calcular(1000, 1000, 'ABERTURA_ATORNILLAR');

        // 2 × (1,0 + 1,0) = 4 m × 12 = 48 + 20 de base
        $this->assertNotNull($resultado);
        $this->assertSame(4.0, $resultado['metros']);
        $this->assertSame(68.0, $resultado['total']);
    }

    public function test_sin_tipo_no_cobra(): void
    {
        $this->assertNull(app(CalculadoraColocacion::class)->calcular(1000, 1000, ''));
        $this->assertNull(app(CalculadoraColocacion::class)->calcular(1000, 1000, null));
        $this->assertNull(app(CalculadoraColocacion::class)->calcular(1000, 1000, 'INVENTADO'));
    }

    private function obraConAbertura(?string $colocacion, int $precio = 200): Obra
    {
        $obra = Obra::query()->create([
            'numero' => 'CO-'.uniqid(), 'nombre' => 'Colocación', 'fechaCreacion' => now()->toDateString(),
        ]);

        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1,
        ]);

        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 12, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => 'Baño', 'tipo' => 'TABAQUERA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => $precio,
            'parametros' => $colocacion === null ? [] : ['colocacion' => $colocacion],
        ]);

        return $obra->fresh();
    }

    public function test_la_colocacion_aparece_como_linea_del_presupuesto(): void
    {
        $obra = $this->obraConAbertura('ABERTURA_ATORNILLAR');

        $lineas = (new LineasPresupuesto)->para($obra);
        $colocacion = collect($lineas)->firstWhere('tipo', 'colocacion');

        $this->assertNotNull($colocacion);
        $this->assertStringContainsString('Colocación', $colocacion['descripcion']);
        $this->assertSame(68.0, $colocacion['subtotal']);
        $this->assertSame(268.0, (new LineasPresupuesto)->total($lineas));
    }

    public function test_sin_colocacion_el_total_es_el_de_la_abertura(): void
    {
        $obra = $this->obraConAbertura(null);

        $lineas = (new LineasPresupuesto)->para($obra);

        $this->assertCount(1, $lineas);
        $this->assertSame(200.0, (new LineasPresupuesto)->total($lineas));
    }

    public function test_el_detalle_de_obra_suma_la_colocacion(): void
    {
        $obra = $this->obraConAbertura('ABERTURA_ATORNILLAR');

        $this->actingAs($this->usuario)
            ->get("/obras/{$obra->id}")
            ->assertOk()
            ->assertSee('268,00');
    }

    public function test_rechaza_un_tipo_de_colocacion_inexistente(): void
    {
        $obra = Obra::query()->create([
            'numero' => 'CO-X', 'nombre' => 'Obra', 'fechaCreacion' => now()->toDateString(),
        ]);

        $this->actingAs($this->usuario)
            ->post("/obras/{$obra->id}/aberturas", [
                'tipo' => 'ABERTURA_SIMPLE',
                'variante' => '12:10',
                'ancho' => 700,
                'alto' => 500,
                'cantidad' => 1,
                'terminacion' => 'PINTADO_BLANCO',
                'colocacion' => 'NO-EXISTE',
            ])
            ->assertSessionHasErrors('colocacion');
    }
}
