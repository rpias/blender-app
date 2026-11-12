<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavegacionBasicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_raiz_redirige_a_obras(): void
    {
        $this->get('/')->assertRedirect('/obras');
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
