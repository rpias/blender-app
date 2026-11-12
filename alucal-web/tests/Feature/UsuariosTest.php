<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
    }

    public function test_la_pantalla_lista_los_usuarios(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/usuarios')
            ->assertOk()
            ->assertSee('Usuarios')
            ->assertSee($admin->email);
    }

    public function test_un_vendedor_no_accede(): void
    {
        $vendedor = User::factory()->create(['nombre' => 'Vend', 'rol' => 'vendedor', 'activo' => true]);

        $this->actingAs($vendedor)->get('/usuarios')->assertForbidden();
    }

    public function test_un_admin_crea_un_usuario(): void
    {
        $this->actingAs($this->admin())
            ->post('/usuarios', [
                'nombre' => 'Nuevo Vendedor',
                'email' => 'nuevo@alucal.local',
                'password' => 'secreto123',
                'rol' => 'vendedor',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'nuevo@alucal.local', 'rol' => 'vendedor', 'activo' => true]);
    }

    public function test_un_admin_cambia_el_rol(): void
    {
        $usuario = User::factory()->create(['nombre' => 'X', 'rol' => 'vendedor', 'activo' => true]);

        $this->actingAs($this->admin())
            ->patch("/usuarios/{$usuario->id}", ['rol' => 'admin', 'activo' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('admin', $usuario->fresh()->rol);
    }

    public function test_no_puede_quitarse_su_propio_rol(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch("/usuarios/{$admin->id}", ['rol' => 'vendedor'])
            ->assertSessionHasErrors('rol');

        $this->assertSame('admin', $admin->fresh()->rol);
    }

    public function test_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete("/usuarios/{$admin->id}")
            ->assertSessionHasErrors('usuario');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_elimina_otro_usuario(): void
    {
        $otro = User::factory()->create(['nombre' => 'Otro', 'rol' => 'vendedor', 'activo' => true]);

        $this->actingAs($this->admin())
            ->delete("/usuarios/{$otro->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $otro->id]);
    }
}
