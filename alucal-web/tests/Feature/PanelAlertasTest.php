<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAlertasTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
    }

    private function obra(array $datos = []): Obra
    {
        return Obra::query()->create(array_merge([
            'numero' => 'A-'.uniqid(),
            'nombre' => 'Obra alerta',
            'fechaCreacion' => now()->toDateString(),
            'estadoSeguimiento' => 'ABIERTA',
        ], $datos));
    }

    private function conAbertura(Obra $obra, int $precio = 100): void
    {
        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1,
        ]);

        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 6, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'MARCO_FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => $precio,
        ]);
    }

    public function test_muestra_la_accion_vencida(): void
    {
        $obra = $this->obra([
            'proximaAccionFecha' => now()->subDays(3)->toDateString(),
            'proximaAccionDetalle' => 'Llamar al cliente',
        ]);
        $this->conAbertura($obra);

        $this->actingAs($this->usuario)
            ->get('/')
            ->assertOk()
            ->assertSee('Próxima acción vencida')
            ->assertSee('Llamar al cliente');
    }

    public function test_no_muestra_una_accion_futura_lejana(): void
    {
        $obra = $this->obra([
            'proximaAccionFecha' => now()->addDays(30)->toDateString(),
            'proximaAccionDetalle' => 'Revisar medidas',
        ]);
        $this->conAbertura($obra);

        $this->actingAs($this->usuario)
            ->get('/')
            ->assertOk()
            ->assertDontSee('Próxima acción vencida')
            ->assertDontSee('Revisar medidas');
    }

    public function test_muestra_la_accion_de_esta_semana(): void
    {
        $obra = $this->obra([
            'proximaAccionFecha' => now()->addDays(2)->toDateString(),
            'proximaAccionDetalle' => 'Entregar presupuesto',
        ]);
        $this->conAbertura($obra);

        $this->actingAs($this->usuario)
            ->get('/')
            ->assertOk()
            ->assertSee('Próxima acción esta semana')
            ->assertSee('Entregar presupuesto');
    }

    public function test_muestra_obras_sin_aberturas(): void
    {
        $this->obra(['nombre' => 'Vacia total']);

        $this->actingAs($this->usuario)
            ->get('/')
            ->assertOk()
            ->assertSee('Obras sin aberturas cargadas')
            ->assertSee('Vacia total');
    }

    public function test_no_alerta_por_una_obra_cerrada_con_accion_vencida(): void
    {
        $obra = $this->obra([
            'estadoSeguimiento' => 'CERRADA',
            'proximaAccionFecha' => now()->subDays(3)->toDateString(),
            'proximaAccionDetalle' => 'No corresponde',
        ]);
        $this->conAbertura($obra);

        $this->actingAs($this->usuario)
            ->get('/')
            ->assertOk()
            ->assertDontSee('Próxima acción vencida');
    }
}
