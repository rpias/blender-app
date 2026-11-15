<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\User;
use App\Servicios\Exportacion\OptimizacionCorte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimizacionCorteTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_sola_barra_si_todo_entra(): void
    {
        $resultado = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 1500, 'cantidad' => 4],
        ], 6000, 0);

        $this->assertCount(1, $resultado);
        $this->assertSame(1, $resultado[0]['totalBarras']);
        $this->assertSame(0.0, $resultado[0]['sobranteMM']);
        $this->assertSame([1500.0, 1500.0, 1500.0, 1500.0], $resultado[0]['barras'][0]['cortes']);
    }

    public function test_dos_barras_cuando_no_entran(): void
    {
        $resultado = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 2000, 'cantidad' => 4],
        ], 6000, 0);

        // Tres tramos en una barra y el cuarto en otra: sobra una barra entera menos 2.000.
        $this->assertSame(2, $resultado[0]['totalBarras']);
        $this->assertSame(4000.0, $resultado[0]['sobranteMM']);
    }

    public function test_reparte_mezclando_medidas(): void
    {
        $resultado = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 3000, 'cantidad' => 2],
            ['codigo' => '9500', 'largoMM' => 2000, 'cantidad' => 3],
        ], 6000, 0);

        // 3000+3000 en una barra y 2000×3 en otra: dos barras justas.
        $this->assertSame(2, $resultado[0]['totalBarras']);
        $this->assertSame(0.0, $resultado[0]['sobranteMM']);
    }

    public function test_la_sierra_consume_largo(): void
    {
        $sinSierra = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 3000, 'cantidad' => 2],
        ], 6000, 0);

        $conSierra = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 3000, 'cantidad' => 2],
        ], 6000, 3);

        // Sin sierra entran justo; con 3 mm por corte ya no entran en una barra.
        $this->assertSame(1, $sinSierra[0]['totalBarras']);
        $this->assertSame(2, $conSierra[0]['totalBarras']);
        $this->assertSame(5994.0, $conSierra[0]['sobranteMM']);
    }

    public function test_un_tramo_mas_largo_que_la_barra_no_entra(): void
    {
        $resultado = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 6500, 'cantidad' => 1],
            ['codigo' => '9500', 'largoMM' => 2000, 'cantidad' => 1],
        ], 6000, 3);

        $this->assertCount(1, $resultado[0]['noEntran']);
        $this->assertSame(6500.0, $resultado[0]['noEntran'][0]['largoMM']);
        $this->assertSame(1, $resultado[0]['totalBarras']);
    }

    public function test_agrupa_por_perfil(): void
    {
        $resultado = app(OptimizacionCorte::class)->para([
            ['codigo' => '9500', 'largoMM' => 1000, 'cantidad' => 1],
            ['codigo' => 'ADR 1471', 'largoMM' => 1000, 'cantidad' => 1],
        ], 6000, 0);

        $this->assertCount(2, $resultado);
        $this->assertSame('9500', $resultado[0]['codigo']);
        $this->assertSame('ADR 1471', $resultado[1]['codigo']);
    }

    public function test_la_pantalla_muestra_la_optimizacion(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
        $obra = Obra::query()->create([
            'numero' => 'O-1', 'nombre' => 'Cortes', 'fechaCreacion' => now()->toDateString(),
        ]);
        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1,
        ]);

        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 12, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'TABAQUERA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => 100,
            'lineas' => [
                'barras' => [['perfil' => '9500', 'pesoKgPorMetro' => 1.0, 'cantidad' => 4, 'largoMM' => 1000, 'parte' => 'MARCO']],
                'accesorios' => [], 'vidrios' => [], 'kilos' => 4,
            ],
        ]);

        $this->actingAs($usuario)
            ->get("/obras/{$obra->id}/computo")
            ->assertOk()
            ->assertSee('Optimización de corte')
            ->assertSee('9500')
            ->assertSee('retazo');
    }
}
