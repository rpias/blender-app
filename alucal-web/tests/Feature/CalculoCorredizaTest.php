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

class CalculoCorredizaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConstantesCalculoSeeder::class);

        $perfiles = [
            '217' => 0.586, '218' => 0.486, '204' => 0.605, '235' => 1.02,
            '202' => 0.432, '203' => 0.467, '2210' => 0.44, '201' => 0.4,
        ];

        foreach ($perfiles as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        $accesorios = [
            '230478' => 35, '230447' => 18, '230453' => 250, '230468' => 80,
            '230464' => 38, '230941' => 9, '100104' => 451, '230237' => 86, '230279' => 92,
        ];

        foreach ($accesorios as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo,
                'precio' => $precio,
                'cantPrecio' => 1,
                'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1,
                'perteneceAListadoAccImportados' => false,
                'stockMinimo' => 0,
                'reposicion' => 0,
                'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 3,
            'idSerie' => 19,
            'MargenMH' => 1.8,
            'MargenMosq' => 2.6,
            'DespMH' => 1.08,
            'DespMosq' => 1.0,
            'CoefAjusteObraParticular' => 1.15,
        ]);

        PreciosPorKilo::query()->create([
            'terminacion' => 'PINTADO_BLANCO',
            'rpt' => false,
            'valor' => 7.48,
        ]);

        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create([
            'id' => 1, 'idTipo' => 1, 'precio' => 12.3,
            'precioMinimo' => 8.0, 'margen' => 2.0,
        ]);

        Serie::query()->create(['id' => 19, 'nombre' => 'Serie 20', 'abreviacion' => 'S20', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(float $ancho, float $alto): AberturaSimple
    {
        $obra = Obra::query()->create([
            'numero' => 'T-C1',
            'nombre' => 'Corrediza',
            'fechaCreacion' => now()->toDateString(),
        ]);

        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE',
            'idObra' => $obra->id,
            'numero' => 1,
        ]);

        return AberturaSimple::query()->create([
            'cantidad' => 1,
            'ancho' => $ancho,
            'alto' => $alto,
            'idTipologia' => 3,
            'idSerie' => 19,
            'terminacion' => 'PINTADO_BLANCO',
            'numero' => 1,
            'colorHerrajes' => '',
            'colorPlasticos' => '',
            'ubicacion' => 'Patio',
            'tipo' => 'CORREDIZA',
            'idElementoObra' => $elemento->id,
            'idRellenoVano' => 1,
        ]);
    }

    public function test_corrediza_s20_dos_hojas_calcula_barras_y_vidrios(): void
    {
        $abertura = $this->abertura(1500, 1000);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marcoLateral' => '235',
            'marcoInferior' => '204',
            'cajaDeAgua' => false,
            'zocalo' => '2210',
            'cabezal' => '2210',
            'enganche' => '203',
            'cantEnganches' => 2,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $lineas = $abertura->lineas;

        // Marco: 2 horizontales de 204 a ancho completo, 2 laterales 235 a alto - 23.
        $this->assertSame('204', $lineas['barras'][0]['perfil']);
        $this->assertSame(1500.0, (float) $lineas['barras'][0]['largoMM']);
        $this->assertSame('235', $lineas['barras'][1]['perfil']);
        $this->assertSame(977.0, (float) $lineas['barras'][1]['largoMM']);

        // Zócalo = cabezal 2210 ⇒ 4 barras de 739 mm (676 manual + 63).
        $this->assertSame('2210', $lineas['barras'][2]['perfil']);
        $this->assertSame(739.0, (float) $lineas['barras'][2]['largoMM']);
        $this->assertSame(4, $lineas['barras'][2]['cantidad']);

        // Hoja lateral 202: alto - 48 = 952 ; enganche 203: alto - 85 = 915.
        $this->assertSame(952.0, (float) $lineas['barras'][3]['largoMM']);
        $this->assertSame(915.0, (float) $lineas['barras'][4]['largoMM']);

        // Dos vidrios de 0,690 × (0,952 - 0,068 - 0,016 zócalo - 0,016 cabezal) = 0,852 m.
        $this->assertCount(1, $lineas['vidrios']);
        $this->assertSame(2, $lineas['vidrios'][0]['cantidad']);
        $this->assertEqualsWithDelta(0.69, $lineas['vidrios'][0]['anchoM'], 0.001);
        $this->assertEqualsWithDelta(0.852, $lineas['vidrios'][0]['altoM'], 0.001);

        $this->assertGreaterThan(0, $resultado = (int) $abertura->precioUnitario);
        $this->assertNotEmpty($lineas['accesorios']);
    }

    public function test_se_agrega_desde_la_obra_con_el_formulario(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);
        $obra = Obra::query()->create(['numero' => 'T-C2', 'nombre' => 'Corrediza web', 'fechaCreacion' => now()->toDateString()]);

        $this->actingAs($usuario)
            ->post("/obras/{$obra->id}/aberturas", [
                'tipo' => 'ABERTURA_SIMPLE',
                'idTipologia' => 3,
                'idSerie' => 19,
                'cantidad' => 1,
                'ancho' => 1500,
                'alto' => 1000,
                'ubicacion' => 'Patio',
                'terminacion' => 'PINTADO_BLANCO',
                'marcoLateral' => '235',
                'marcoInferior' => '204',
                'zocalo' => '2210',
                'cabezal' => '2210',
                'enganche' => '203',
                'vidrioId' => 1,
            ])
            ->assertRedirect();

        $abertura = AberturaSimple::query()->firstOrFail();

        $this->assertSame(3, (int) $abertura->idTipologia);
        $this->assertSame('CORREDIZA', $abertura->tipo);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_falla_si_el_marco_lateral_no_es_valido(): void
    {
        $abertura = $this->abertura(1500, 1000);

        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marcoLateral' => '999',
            'marcoInferior' => '204',
            'zocalo' => '2210',
            'cabezal' => '2210',
            'enganche' => '203',
        ]);
    }
}
