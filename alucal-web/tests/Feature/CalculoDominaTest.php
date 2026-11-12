<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\Accesorio;
use App\Models\ElementoObra;
use App\Models\MargenesDesperdiciosCoefAjuste;
use App\Models\Obra;
use App\Models\Perfil;
use App\Models\PreciosPorKilo;
use App\Models\Serie;
use App\Models\TipoVidrio;
use App\Models\User;
use App\Models\VidrioSinCamara;
use App\Services\Aberturas\CalcularAberturaSimple;
use Database\Seeders\ConstantesCalculoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculoDominaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConstantesCalculoSeeder::class);

        foreach (['9500' => 1.115, '9525' => 0.751, '9520' => 0.845, '9530' => 0.757, '9540' => 1.428, '9580' => 0.218, '9527' => 0.853] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach (['230420' => 92, '230049' => 20, '230087' => 15, '231001' => 530, '230637' => 41, '230639' => 48, '230632' => 69, '230829' => 20, '191280' => 732, '193155' => 250] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 3, 'idSerie' => 2, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 2, 'nombre' => 'Domina', 'abreviacion' => 'DOM', 'rpt' => false, 'activa' => true]);
    }

    public function test_domina_dos_hojas_calcula_marco_hojas_y_vidrio(): void
    {
        $obra = Obra::query()->create(['numero' => 'D-1', 'nombre' => 'Domina', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        $abertura = AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 3, 'idSerie' => 2,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => 'Living', 'tipo' => 'CORREDIZA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'enganche' => 'SIMPLE',
            'ruedaCodigo' => '193155',
            'divisorAlMedio' => false,
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $lineas = $abertura->lineas;

        // Marco perimetral 9500 a medida nominal.
        $this->assertSame('9500', $lineas['barras'][0]['perfil']);
        $this->assertSame(1000.0, (float) $lineas['barras'][0]['largoMM']);

        // Hoja: 9525 a alto - 64 = 936 ; 9520 ×4 a ancho/2 - 25 = 475.
        $this->assertSame(936.0, (float) $lineas['barras'][2]['largoMM']);
        $this->assertSame(475.0, (float) $lineas['barras'][3]['largoMM']);
        $this->assertSame(4, $lineas['barras'][3]['cantidad']);

        // Parante central simple: 2 × 936.
        $this->assertSame('9530', $lineas['barras'][4]['perfil']);

        // Vidrio: (475 - 79) × (936 - 111) = 396 × 825 mm ⇒ 0,396 × 0,825 m, 2 piezas.
        $this->assertSame(2, $lineas['vidrios'][0]['cantidad']);
        $this->assertEqualsWithDelta(0.396, $lineas['vidrios'][0]['anchoM'], 0.001);
        $this->assertEqualsWithDelta(0.825, $lineas['vidrios'][0]['altoM'], 0.001);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
        $this->assertNotEmpty($lineas['accesorios']);
    }

    public function test_se_carga_desde_el_formulario_de_la_obra(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);
        $obra = Obra::query()->create(['numero' => 'D-2', 'nombre' => 'Domina web', 'fechaCreacion' => now()->toDateString()]);

        $this->actingAs($usuario)
            ->post("/obras/{$obra->id}/aberturas", [
                'tipo' => 'ABERTURA_SIMPLE',
                'idTipologia' => 3,
                'idSerie' => 2,
                'cantidad' => 1,
                'ancho' => 1500,
                'alto' => 1000,
                'ubicacion' => 'Dormitorio',
                'terminacion' => 'PINTADO_BLANCO',
                'engancheDomina' => 'REFORZADO',
                'vidrioId' => 1,
            ])
            ->assertRedirect();

        $abertura = AberturaSimple::query()->firstOrFail();

        $this->assertSame(2, (int) $abertura->idSerie);
        $this->assertSame('REFORZADO', $abertura->parametros['enganche']);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);

        // El parante reforzado usa 9530 + 9540.
        $perfiles = collect($abertura->lineas['barras'])->pluck('perfil')->all();
        $this->assertContains('9530', $perfiles);
        $this->assertContains('9540', $perfiles);
    }
}
