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
use App\Services\Aberturas\SerieSinConstantes;
use Database\Seeders\ConstantesCalculoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculoHA62Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConstantesCalculoSeeder::class);

        $perfiles = [
            'ADR 2464' => 1.2, 'ADR 1770' => 0.7, 'ADR 2465' => 0.5,
            'ADR 1774' => 0.2, 'ADR 1767' => 1.1, 'ADR 1769' => 1.0, 'ADR 1771' => 0.62,
        ];

        foreach ($perfiles as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        $accesorios = [
            '230045' => 40, '230049' => 20, '230087' => 15, '230419' => 60, '230374' => 30,
            '230158' => 200, '230340' => 5, '230410' => 8, '230372' => 12, '230360' => 6,
            '230828' => 9, '230640' => 11, '230233' => 80, '231007' => 90, '230574' => 95, '230236' => 120,
            '193580' => 50, '193600' => 55, '230361' => 45,
        ];

        foreach ($accesorios as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 3, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'H-1', 'nombre' => 'HA62', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 3, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => 'Living', 'tipo' => 'CORREDIZA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_ha62_con_marco_a_45_grados(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marco' => '45 GRADOS',
            'enganche' => 'SIMPLE',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $lineas = $abertura->lineas;

        // Marco ADR 2464: 2 × 1000 horizontal (umbral/dintel) y 2 × 1000 vertical.
        $this->assertSame('ADR 2464', $lineas['barras'][0]['perfil']);
        $this->assertSame(1000.0, (float) $lineas['barras'][0]['largoMM']);

        // Hoja: alto - 76 = 924 ; zócalo = 1000/2 - 2 = 498.
        $this->assertSame(924.0, (float) $lineas['barras'][3]['largoMM']);
        $this->assertSame(498.0, (float) $lineas['barras'][2]['largoMM']);

        // Vidrio: (498 - 123) × (924 - 123) mm = 375 × 801 mm, 2 piezas.
        $this->assertSame(2, $lineas['vidrios'][0]['cantidad']);
        $this->assertEqualsWithDelta(0.375, $lineas['vidrios'][0]['anchoM'], 0.001);
        $this->assertEqualsWithDelta(0.801, $lineas['vidrios'][0]['altoM'], 0.001);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_ha62_con_marco_a_90_grados_usa_otras_jambas(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marco' => '90 GRADOS',
            'enganche' => 'SIMPLE',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $lineas = $abertura->lineas;

        $this->assertSame('ADR 1767', $lineas['barras'][0]['perfil']);
        $this->assertSame(952.0, (float) $lineas['barras'][0]['largoMM']); // ancho - 48
        $this->assertSame('ADR 1769', $lineas['barras'][1]['perfil']);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_se_carga_desde_el_formulario_de_la_obra(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);
        $obra = Obra::query()->create(['numero' => 'H-2', 'nombre' => 'HA62 web', 'fechaCreacion' => now()->toDateString()]);

        $this->actingAs($usuario)
            ->post("/obras/{$obra->id}/aberturas", [
                'tipo' => 'ABERTURA_SIMPLE',
                'idTipologia' => 3,
                'idSerie' => 10,
                'cantidad' => 1,
                'ancho' => 1500,
                'alto' => 1000,
                'ubicacion' => 'Cocina',
                'terminacion' => 'PINTADO_BLANCO',
                'marcoHA62' => '90 GRADOS',
                'engancheHA62' => 'REFORZADO',
                'vidrioId' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $abertura = AberturaSimple::query()->latest('id')->firstOrFail();

        $this->assertSame('90 GRADOS', $abertura->parametros['marco']);
        $this->assertSame('REFORZADO', $abertura->parametros['enganche']);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_ha62_rechaza_un_parante_desconocido(): void
    {
        $abertura = $this->abertura();

        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marco' => '45 GRADOS',
            'enganche' => 'RARO',
            'vidrioId' => 1,
        ]);
    }
}
