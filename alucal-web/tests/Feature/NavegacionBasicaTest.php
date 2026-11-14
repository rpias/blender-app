<?php

namespace Tests\Feature;

use App\Models\Obra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavegacionBasicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_obra_muestra_el_formulario_unico_con_las_variantes(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
        $obra = Obra::query()->create([
            'numero' => 'N-9', 'nombre' => 'Formulario', 'fechaCreacion' => now()->toDateString(),
        ]);

        $respuesta = $this->actingAs($usuario)->get("/obras/{$obra->id}");

        $respuesta->assertOk()
            ->assertSee('Agregar abertura')
            ->assertSee('value="15:3"', false)
            ->assertSee('value="23:44"', false)
            ->assertSee('grupo-variante', false);
    }

    public function test_la_raiz_lleva_al_panel_y_pide_sesion(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_el_panel_muestra_los_indicadores(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);

        $this->actingAs($usuario)
            ->get('/')
            ->assertOk()
            ->assertSee('Obras abiertas')
            ->assertSee('Presupuestado')
            ->assertSee('Aberturas cargadas')
            ->assertSee('Tipologías más usadas');
    }

    public function test_un_visitante_no_ve_obras(): void
    {
        $this->get('/obras')->assertRedirect('/login');
    }

    public function test_el_login_se_muestra(): void
    {
        $this->get('/login')->assertOk()->assertSee('Ingresar');
    }

    public function test_un_usuario_autenticado_ve_el_listado_de_obras(): void
    {
        $usuario = User::factory()->create([
            'nombre' => 'Tester',
            'rol' => 'admin',
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->get('/obras')
            ->assertOk()
            ->assertSee('Obras');
    }
}
