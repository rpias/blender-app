<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\Cliente;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginacionTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
    }

    public function test_las_obras_se_paginan_de_a_veinte(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            Obra::query()->create([
                'numero' => 'P-'.$i,
                'nombre' => 'Obra '.$i,
                'fechaCreacion' => now()->toDateString(),
                'estadoSeguimiento' => 'ABIERTA',
            ]);
        }

        $this->actingAs($this->usuario)
            ->get('/obras')
            ->assertOk()
            ->assertSee('Obra 1')
            ->assertDontSee('Obra 25')
            ->assertSee('Siguiente');

        $this->actingAs($this->usuario)
            ->get('/obras?page=2')
            ->assertOk()
            ->assertSee('Obra 25')
            ->assertSee('Anterior');
    }

    public function test_los_clientes_se_paginan_de_a_veinticinco(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            Cliente::query()->create(['numero' => $i, 'nombre' => 'Cliente '.$i]);
        }

        $this->actingAs($this->usuario)
            ->get('/clientes')
            ->assertOk()
            ->assertSee('Cliente 1')
            ->assertDontSee('Cliente 9<');

        $this->actingAs($this->usuario)
            ->get('/clientes?page=2')
            ->assertOk()
            ->assertSee('Cliente 9')
            ->assertSee('26–30 de 30');
    }

    public function test_los_usuarios_se_paginan_de_a_veinticinco(): void
    {
        for ($i = 1; $i <= 28; $i++) {
            User::factory()->create(['nombre' => sprintf('U%02d', $i), 'rol' => 'vendedor', 'activo' => true]);
        }

        $this->actingAs($this->usuario)
            ->get('/usuarios')
            ->assertOk()
            ->assertSee('Siguiente');

        $this->actingAs($this->usuario)
            ->get('/usuarios?page=2')
            ->assertOk()
            ->assertSee('Anterior');
    }

    public function test_los_elementos_de_la_obra_se_paginan_de_a_quince(): void
    {
        $obra = Obra::query()->create([
            'numero' => 'E-1', 'nombre' => 'Elementos', 'fechaCreacion' => now()->toDateString(),
        ]);

        for ($i = 1; $i <= 18; $i++) {
            $elemento = ElementoObra::query()->create([
                'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => $i,
            ]);

            AberturaSimple::query()->create([
                'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 6, 'idSerie' => 10,
                'terminacion' => 'PINTADO_BLANCO', 'numero' => $i, 'colorHerrajes' => '', 'colorPlasticos' => '',
                'ubicacion' => '', 'tipo' => 'MARCO_FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
                'precioUnitario' => 100,
            ]);
        }

        $this->actingAs($this->usuario)
            ->get("/obras/{$obra->id}")
            ->assertOk()
            ->assertSee('Siguiente');

        $this->actingAs($this->usuario)
            ->get("/obras/{$obra->id}?pagina=2")
            ->assertOk()
            ->assertSee('Anterior');
    }

    public function test_la_busqueda_se_conserva_en_el_paginador(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            Obra::query()->create([
                'numero' => 'B-'.$i,
                'nombre' => 'Buscable '.$i,
                'fechaCreacion' => now()->toDateString(),
                'estadoSeguimiento' => 'ABIERTA',
            ]);
        }

        $respuesta = $this->actingAs($this->usuario)->get('/obras?buscar=Buscable');

        $respuesta->assertOk();
        $this->assertStringContainsString('buscar=Buscable', $respuesta->getContent());
    }
}
