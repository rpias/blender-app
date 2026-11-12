<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Precios\Coeficientes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogosTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);

        DB::table('coeficientes_de_calculo_de_precio')->insert([
            ['clave' => 'MARGEN_DVH_CARPINTERIA', 'valor' => '1.22'],
            ['clave' => 'COEF_DESP_VIDRIO', 'valor' => '1.2'],
        ]);

        DB::table('margenes_desperdicios_coef_ajuste')->insert([
            'idTipologia' => 3, 'idSerie' => 2, 'MargenMH' => 1.8, 'DespMH' => 1.08,
            'MargenMosq' => 2.6, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);

        DB::table('perfil')->insert([
            ['Numero' => 'ADR 1470', 'Peso' => 1.018, 'Descripcion' => 'MARCO BATIENTE'],
            ['Numero' => 'ADR 916', 'Peso' => 0.256, 'Descripcion' => 'CONTRAVIDRIO RECTO'],
        ]);

        DB::table('accesorio')->insert([
            ['codigo' => '230478', 'descripcion' => 'DISPOSITIVO', 'precio' => 35, 'cantPrecio' => 1,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0],
            ['codigo' => '230447', 'descripcion' => 'PROTECTOR', 'precio' => 18, 'cantPrecio' => 1,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0],
        ]);
    }

    public function test_un_visitante_no_entra_a_los_catalogos(): void
    {
        $this->get('/catalogos/coeficientes')->assertRedirect('/login');
    }

    public function test_muestra_los_coeficientes_con_su_valor(): void
    {
        $this->actingAs($this->usuario)
            ->get('/catalogos/coeficientes')
            ->assertOk()
            ->assertSee('MARGEN_DVH_CARPINTERIA')
            ->assertSee('1.22');
    }

    public function test_guarda_un_coeficiente_y_el_motor_lo_lee(): void
    {
        $this->actingAs($this->usuario)
            ->post('/catalogos/coeficientes', [
                'filas' => [
                    'MARGEN_DVH_CARPINTERIA' => ['valor' => '1.5'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('1.5', DB::table('coeficientes_de_calculo_de_precio')
            ->where('clave', 'MARGEN_DVH_CARPINTERIA')->value('valor'));

        // El servicio de precios toma el valor nuevo de la base.
        $this->assertEqualsWithDelta(1.5, (new Coeficientes)->coeficiente('MARGEN_DVH_CARPINTERIA'), 0.001);
    }

    public function test_rechaza_un_valor_no_numerico_y_no_modifica_la_base(): void
    {
        $this->actingAs($this->usuario)
            ->post('/catalogos/coeficientes', [
                'filas' => [
                    'MARGEN_DVH_CARPINTERIA' => ['valor' => 'abc'],
                ],
            ])
            ->assertSessionHasErrors('filas.*.valor');

        $this->assertSame('1.22', DB::table('coeficientes_de_calculo_de_precio')
            ->where('clave', 'MARGEN_DVH_CARPINTERIA')->value('valor'));
    }

    public function test_actualiza_el_precio_de_un_accesorio(): void
    {
        $id = DB::table('accesorio')->where('codigo', '230478')->value('id');

        $this->actingAs($this->usuario)
            ->post('/catalogos/accesorios', [
                'filas' => [
                    (string) $id => ['precio' => '99', 'cantPrecio' => '1'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(99, (int) DB::table('accesorio')->where('id', $id)->value('precio'));
    }

    public function test_rechaza_precio_negativo_en_accesorios(): void
    {
        $id = DB::table('accesorio')->where('codigo', '230478')->value('id');

        $this->actingAs($this->usuario)
            ->post('/catalogos/accesorios', [
                'filas' => [
                    (string) $id => ['precio' => '-5', 'cantPrecio' => '1'],
                ],
            ])
            ->assertSessionHasErrors('filas.*.precio');

        $this->assertSame(35, (int) DB::table('accesorio')->where('id', $id)->value('precio'));
    }

    public function test_actualiza_una_fila_con_clave_compuesta(): void
    {
        $this->actingAs($this->usuario)
            ->post('/catalogos/margenes', [
                'filas' => [
                    '3:2' => ['MargenMH' => '2.1', 'DespMH' => '1.09', 'MargenMosq' => '2.6', 'DespMosq' => '1'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertEqualsWithDelta(2.1, (float) DB::table('margenes_desperdicios_coef_ajuste')
            ->where('idTipologia', 3)->where('idSerie', 2)->value('MargenMH'), 0.001);
    }

    public function test_busca_perfiles_por_numero(): void
    {
        $respuesta = $this->actingAs($this->usuario)->get('/catalogos/perfiles?buscar=1470');

        $respuesta->assertOk()->assertSee('ADR 1470');
        $this->assertStringNotContainsString('ADR 916', $respuesta->getContent());
    }

    public function test_un_vendedor_no_puede_entrar_ni_guardar(): void
    {
        $vendedor = User::factory()->create(['nombre' => 'Vendedor', 'rol' => 'vendedor', 'activo' => true]);

        $this->actingAs($vendedor)->get('/catalogos/coeficientes')->assertForbidden();
        $this->actingAs($vendedor)->post('/catalogos/coeficientes', [
            'filas' => ['MARGEN_DVH_CARPINTERIA' => ['valor' => '9']],
        ])->assertForbidden();

        $this->assertSame('1.22', DB::table('coeficientes_de_calculo_de_precio')
            ->where('clave', 'MARGEN_DVH_CARPINTERIA')->value('valor'));
    }

    public function test_el_vendedor_no_ve_el_enlace_de_catalogos(): void
    {
        $vendedor = User::factory()->create(['nombre' => 'Vendedor', 'rol' => 'vendedor', 'activo' => true]);

        $this->actingAs($vendedor)->get('/obras')
            ->assertOk()
            ->assertDontSee('>Catálogos</a>', false);
    }

    public function test_agrega_un_accesorio(): void
    {
        $this->actingAs($this->usuario)
            ->post('/catalogos/accesorios/nueva', [
                'codigo' => '999999',
                'descripcion' => 'ACCESORIO NUEVO',
                'precio' => '45',
                'cantPrecio' => '2',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(45, (int) DB::table('accesorio')->where('codigo', '999999')->value('precio'));
    }

    public function test_rechaza_un_codigo_de_accesorio_repetido(): void
    {
        $this->actingAs($this->usuario)
            ->post('/catalogos/accesorios/nueva', [
                'codigo' => '230478',
                'descripcion' => 'REPETIDO',
                'precio' => '10',
                'cantPrecio' => '1',
            ])
            ->assertSessionHasErrors('codigo');
    }

    public function test_elimina_un_perfil(): void
    {
        $id = DB::table('perfil')->where('Numero', 'ADR 916')->value('id');

        $this->actingAs($this->usuario)
            ->delete("/catalogos/perfiles/{$id}")
            ->assertRedirect();

        $this->assertSame(0, DB::table('perfil')->where('id', $id)->count());
    }

    public function test_un_catalogo_sin_alta_no_permite_crear(): void
    {
        $this->actingAs($this->usuario)
            ->post('/catalogos/coeficientes/nueva', ['clave' => 'X', 'valor' => '1'])
            ->assertNotFound();
    }

    public function test_recurso_inexistente_da_404(): void
    {
        $this->actingAs($this->usuario)->get('/catalogos/no-existe')->assertNotFound();
    }
}
