<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdicionObraTest extends TestCase
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
            'numero' => 'E-1',
            'nombre' => 'Casa',
            'ubicacion' => 'Solymar',
            'fechaCreacion' => now()->toDateString(),
            'estadoSeguimiento' => 'ABIERTA',
        ]);
    }

    public function test_muestra_el_formulario_de_edicion(): void
    {
        $obra = $this->obra();

        $this->actingAs($this->usuario)
            ->get("/obras/{$obra->id}/editar")
            ->assertOk()
            ->assertSee('Editar obra E-1');
    }

    public function test_actualiza_los_datos_de_la_obra(): void
    {
        $obra = $this->obra();

        $this->actingAs($this->usuario)
            ->patch("/obras/{$obra->id}", [
                'numero' => 'E-1',
                'nombre' => 'Casa de playa',
                'ubicacion' => 'Atlántida',
                'estadoSeguimiento' => 'CERRADA',
                'proximaAccionFecha' => '2026-12-01',
            ])
            ->assertRedirect("/obras/{$obra->id}");

        $obra->refresh();

        $this->assertSame('Casa de playa', $obra->nombre);
        $this->assertSame('Atlántida', $obra->ubicacion);
        $this->assertSame('CERRADA', $obra->estadoSeguimiento);
    }

    public function test_no_permite_repetir_el_numero_de_otra_obra(): void
    {
        $obra = $this->obra();
        Obra::query()->create(['numero' => 'E-2', 'nombre' => 'Otra', 'fechaCreacion' => now()->toDateString()]);

        $this->actingAs($this->usuario)
            ->patch("/obras/{$obra->id}", ['numero' => 'E-2', 'nombre' => 'Casa'])
            ->assertSessionHasErrors('numero');
    }

    public function test_elimina_la_obra_con_sus_elementos(): void
    {
        $obra = $this->obra();
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);
        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 6, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'MARCO_FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);

        $this->actingAs($this->usuario)
            ->delete("/obras/{$obra->id}")
            ->assertRedirect('/obras');

        $this->assertSame(0, Obra::query()->count());
        $this->assertSame(0, ElementoObra::query()->count());
    }
}
