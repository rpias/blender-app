<?php

namespace Tests\Feature;

use App\Models\Obra;
use App\Models\RegistroSuceso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroSucesosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $password = 'password'): User
    {
        return User::factory()->create([
            'nombre' => 'Admin', 'email' => 'admin@alucal.local', 'password' => $password,
            'rol' => 'admin', 'activo' => true,
        ]);
    }

    public function test_una_obra_creada_queda_registrada(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/obras', [
            'numero' => 'R-1',
            'nombre' => 'Obra registrada',
            'fechaCreacion' => now()->toDateString(),
        ]);

        $suceso = RegistroSuceso::query()->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($suceso);
        $this->assertSame('obra', $suceso->entidad);
        $this->assertSame($admin->id, (int) $suceso->usuario_id);
        $this->assertSame('Admin', $suceso->usuario_nombre);
        $this->assertNotNull($suceso->ip);
        $this->assertDatabaseHas('obra', ['numero' => 'R-1']);
    }

    public function test_el_ingreso_queda_registrado_sin_contrasena(): void
    {
        $admin = $this->admin('secreto-super-seguro');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secreto-super-seguro',
        ]);

        $suceso = RegistroSuceso::query()->where('accion', 'ingresar')->first();

        $this->assertNotNull($suceso);
        $this->assertSame($admin->email, $suceso->datos['email'] ?? null);

        $volcado = json_encode(RegistroSuceso::query()->get()->toArray());
        $this->assertStringNotContainsString('secreto-super-seguro', (string) $volcado);
    }

    public function test_el_intento_fallido_queda_registrado(): void
    {
        $this->admin();

        $this->post('/login', [
            'email' => 'admin@alucal.local',
            'password' => 'incorrecta',
        ]);

        $this->assertDatabaseHas('registro_sucesos', [
            'accion' => 'intento_fallido',
            'entidad' => 'sesion',
        ]);
    }

    public function test_un_vendedor_no_ve_el_registro(): void
    {
        $vendedor = User::factory()->create(['nombre' => 'Vend', 'rol' => 'vendedor', 'activo' => true]);

        $this->actingAs($vendedor)->get('/registro')->assertForbidden();
    }

    public function test_el_admin_consulta_y_filtra(): void
    {
        $admin = $this->admin();

        RegistroSuceso::query()->create([
            'usuario_id' => $admin->id, 'usuario_nombre' => 'Admin', 'accion' => 'eliminar',
            'entidad' => 'obra', 'entidad_id' => 7, 'descripcion' => 'DELETE obras/7',
        ]);
        RegistroSuceso::query()->create([
            'usuario_id' => $admin->id, 'usuario_nombre' => 'Admin', 'accion' => 'crear',
            'entidad' => 'abertura', 'descripcion' => 'POST obras/7/aberturas',
        ]);

        $this->actingAs($admin)
            ->get('/registro')
            ->assertOk()
            ->assertSee('Registro de sucesos')
            ->assertSee('DELETE obras/7')
            ->assertSee('POST obras/7/aberturas');

        $this->actingAs($admin)
            ->get('/registro?accion=eliminar')
            ->assertOk()
            ->assertSee('DELETE obras/7')
            ->assertDontSee('POST obras/7/aberturas');

        $this->actingAs($admin)
            ->get('/registro?entidad=abertura')
            ->assertOk()
            ->assertSee('POST obras/7/aberturas')
            ->assertDontSee('DELETE obras/7');
    }

    public function test_el_registro_se_pagina_conservando_el_filtro(): void
    {
        $admin = $this->admin();

        for ($i = 1; $i <= 40; $i++) {
            RegistroSuceso::query()->create([
                'usuario_id' => $admin->id, 'usuario_nombre' => 'Admin', 'accion' => 'editar',
                'entidad' => 'obra', 'descripcion' => 'Suceso '.$i,
            ]);
        }

        $respuesta = $this->actingAs($admin)->get('/registro?accion=editar');

        $respuesta->assertOk()->assertSee('Siguiente');
        $this->assertStringContainsString('accion=editar', $respuesta->getContent());

        $this->actingAs($admin)->get('/registro?accion=editar&page=2')->assertOk()->assertSee('Anterior');
    }

    public function test_eliminar_una_obra_queda_registrada(): void
    {
        $admin = $this->admin();
        $obra = Obra::query()->create([
            'numero' => 'R-9', 'nombre' => 'Para borrar', 'fechaCreacion' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->delete("/obras/{$obra->id}");

        $this->assertDatabaseHas('registro_sucesos', [
            'accion' => 'eliminar',
            'entidad' => 'obra',
        ]);
    }
}
