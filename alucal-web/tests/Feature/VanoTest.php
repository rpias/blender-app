<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\Perfil;
use App\Models\PreciosPorKilo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VanoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);

        Perfil::query()->create(['Numero' => 'ADR 5000', 'Peso' => 1.0, 'Descripcion' => 'CONECTOR']);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 10]);
    }

    private function obra(string $numero = 'V-1'): Obra
    {
        return Obra::query()->create(['numero' => $numero, 'nombre' => 'Vano', 'fechaCreacion' => now()->toDateString()]);
    }

    private function elemento(Obra $obra, int $numero, int $precio): ElementoObra
    {
        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => $numero,
        ]);

        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 6, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => $numero, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'MARCO_FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => $precio,
        ]);

        return $elemento;
    }

    public function test_crea_un_vano_con_dos_aberturas(): void
    {
        $obra = $this->obra();
        $a = $this->elemento($obra, 1, 100);
        $b = $this->elemento($obra, 2, 200);

        $this->actingAs($this->usuario)
            ->post("/obras/{$obra->id}/vanos", [
                'hijos' => [$a->id, $b->id],
                'codigoConector' => 'ADR 5000',
                'orientacion' => 'VERTICAL',
                'posicionMM' => 2000,
                'terminacion' => 'PINTADO_BLANCO',
            ])
            ->assertRedirect("/obras/{$obra->id}")
            ->assertSessionHasNoErrors();

        $vano = ElementoObra::query()->where('tipoElemento', 'ABERTURA_COMPUESTA')->firstOrFail();

        $this->assertSame($vano->id, $a->fresh()->idElementoVano);
        $this->assertSame($vano->id, $b->fresh()->idElementoVano);
        $this->assertSame(1, $vano->vanoDivisiones()->count());
    }

    public function test_rechaza_una_abertura_de_otra_obra(): void
    {
        $obra = $this->obra('V-1');
        $otra = $this->obra('V-2');
        $a = $this->elemento($obra, 1, 100);
        $ajena = $this->elemento($otra, 1, 100);

        $this->actingAs($this->usuario)
            ->post("/obras/{$obra->id}/vanos", [
                'hijos' => [$a->id, $ajena->id],
                'codigoConector' => 'ADR 5000',
                'orientacion' => 'VERTICAL',
                'posicionMM' => 2000,
            ])
            ->assertSessionHasErrors('hijos');

        $this->assertSame(0, ElementoObra::query()->where('tipoElemento', 'ABERTURA_COMPUESTA')->count());
    }

    public function test_rechaza_una_abertura_ya_agrupada(): void
    {
        $obra = $this->obra();
        $a = $this->elemento($obra, 1, 100);
        $b = $this->elemento($obra, 2, 200);

        $this->actingAs($this->usuario)->post("/obras/{$obra->id}/vanos", [
            'hijos' => [$a->id],
            'codigoConector' => 'ADR 5000',
            'orientacion' => 'VERTICAL',
            'posicionMM' => 2000,
        ]);

        $this->actingAs($this->usuario)
            ->post("/obras/{$obra->id}/vanos", [
                'hijos' => [$a->id, $b->id],
                'codigoConector' => 'ADR 5000',
                'orientacion' => 'VERTICAL',
                'posicionMM' => 2000,
            ])
            ->assertSessionHasErrors('hijos');
    }

    public function test_deshacer_el_vano_conserva_las_aberturas(): void
    {
        $obra = $this->obra();
        $a = $this->elemento($obra, 1, 100);
        $b = $this->elemento($obra, 2, 200);

        $this->actingAs($this->usuario)->post("/obras/{$obra->id}/vanos", [
            'hijos' => [$a->id, $b->id],
            'codigoConector' => 'ADR 5000',
            'orientacion' => 'VERTICAL',
            'posicionMM' => 2000,
        ]);

        $vano = ElementoObra::query()->where('tipoElemento', 'ABERTURA_COMPUESTA')->firstOrFail();

        $this->actingAs($this->usuario)
            ->delete("/obras/{$obra->id}/vanos/{$vano->id}")
            ->assertRedirect("/obras/{$obra->id}");

        $this->assertDatabaseMissing('elemento_obra', ['id' => $vano->id]);
        $this->assertSame(2, ElementoObra::query()->whereNull('idElementoVano')->count());
        $this->assertDatabaseHas('abertura_simple', ['idElementoObra' => $a->id]);
    }
}
