<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\User;
use App\Servicios\Exportacion\ComputoMateriales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputoMaterialesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
    }

    private function obra(): Obra
    {
        return Obra::query()->create([
            'numero' => 'C-1', 'nombre' => 'Cómputo', 'fechaCreacion' => now()->toDateString(),
        ]);
    }

    private function abertura(Obra $obra, int $numero, array $lineas): AberturaSimple
    {
        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => $numero,
        ]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 3, 'idSerie' => 2,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => $numero, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'CORREDIZA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => 100, 'lineas' => $lineas,
        ]);
    }

    private function lineas(float $largo, int $cantidad): array
    {
        return [
            'barras' => [
                ['perfil' => '9500', 'pesoKgPorMetro' => 1.0, 'cantidad' => $cantidad, 'largoMM' => $largo, 'parte' => 'MARCO'],
            ],
            'accesorios' => [
                ['codigo' => '230420', 'precioUnitario' => 0.92, 'cantidad' => 4, 'parte' => 'MARCO'],
            ],
            'vidrios' => [
                ['tipo' => 'simple', 'cantidad' => 1, 'anchoM' => 0.5, 'altoM' => 1.0, 'costoM2' => 12.3],
            ],
            'kilos' => 2.5,
        ];
    }

    public function test_agrupa_perfiles_por_medida_de_corte(): void
    {
        $obra = $this->obra();
        $this->abertura($obra, 1, $this->lineas(1000, 2));
        $this->abertura($obra, 2, $this->lineas(1000, 2));
        $this->abertura($obra, 3, $this->lineas(1500, 1));

        $computo = app(ComputoMateriales::class)->para($obra->fresh());

        $this->assertCount(2, $computo['perfiles']);

        $de1000 = collect($computo['perfiles'])->firstWhere('largoMM', 1000.0);
        $this->assertSame(4, $de1000['cantidad']);
        $this->assertSame(4.0, $de1000['metros']);
        $this->assertSame(4.0, $de1000['kilos']);

        $de1500 = collect($computo['perfiles'])->firstWhere('largoMM', 1500.0);
        $this->assertSame(1, $de1500['cantidad']);
        $this->assertSame(1.5, $de1500['metros']);
    }

    public function test_suma_accesorios_y_vidrios(): void
    {
        $obra = $this->obra();
        $this->abertura($obra, 1, $this->lineas(1000, 1));
        $this->abertura($obra, 2, $this->lineas(1000, 1));

        $computo = app(ComputoMateriales::class)->para($obra->fresh());

        $this->assertCount(1, $computo['accesorios']);
        $this->assertSame(8.0, $computo['accesorios'][0]['cantidad']);

        $this->assertCount(1, $computo['vidrios']);
        $this->assertSame(2, $computo['vidrios'][0]['cantidad']);
        $this->assertSame(1.0, $computo['vidrios'][0]['m2']);
        $this->assertSame(5.0, $computo['kilos']);
        $this->assertSame(2, $computo['aberturas']);
    }

    public function test_la_pantalla_muestra_el_computo(): void
    {
        $obra = $this->obra();
        $this->abertura($obra, 1, $this->lineas(1000, 2));

        $this->actingAs($this->usuario)
            ->get("/obras/{$obra->id}/computo")
            ->assertOk()
            ->assertSee('Cómputo de materiales')
            ->assertSee('9500')
            ->assertSee('Lista de corte')
            ->assertSee('2,00');
    }

    public function test_exporta_el_computo_en_excel(): void
    {
        $obra = $this->obra();
        $this->abertura($obra, 1, $this->lineas(1000, 2));

        $respuesta = $this->actingAs($this->usuario)->get("/obras/{$obra->id}/computo.xlsx");

        $respuesta->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $respuesta->headers->get('content-type'));
    }

    public function test_una_obra_sin_aberturas_no_rompe(): void
    {
        $obra = $this->obra();

        $this->actingAs($this->usuario)
            ->get("/obras/{$obra->id}/computo")
            ->assertOk()
            ->assertSee('La obra no tiene perfiles calculados');
    }
}
