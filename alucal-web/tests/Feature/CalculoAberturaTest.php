<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
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

class CalculoAberturaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConstantesCalculoSeeder::class);

        Perfil::query()->create(['Numero' => 'ADR 1470', 'Peso' => 1.018, 'Descripcion' => 'MARCO BATIENTE']);
        Perfil::query()->create(['Numero' => 'ADR 916', 'Peso' => 0.256, 'Descripcion' => 'CONTRAVIDRIO RECTO 26 MM']);

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 6,
            'idSerie' => 10,
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
            'id' => 1,
            'idTipo' => 1,
            'precio' => 12.3,
            'precioMinimo' => 8.0,
            'margen' => 2.0,
        ]);

        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(float $ancho = 1000, float $alto = 1000): AberturaSimple
    {
        $obra = Obra::query()->create([
            'numero' => 'T-1',
            'nombre' => 'Obra de prueba',
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
            'idTipologia' => 6,
            'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO',
            'numero' => 1,
            'colorHerrajes' => '',
            'colorPlasticos' => '',
            'ubicacion' => 'Living',
            'tipo' => 'FIJO',
            'idElementoObra' => $elemento->id,
            'idRellenoVano' => 1,
        ]);
    }

    public function test_marco_fijo_calcula_marco_contravidrios_y_vidrio(): void
    {
        $abertura = $this->abertura();

        $resultado = app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'perfilMarco' => 'ADR 1470',
            'perfilContravidrio' => 'ADR 916',
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $lineas = $abertura->lineas;

        // 2 barras horizontales + 2 verticales de marco.
        $this->assertSame(1000.0, (float) $lineas['barras'][0]['largoMM']);
        $this->assertSame(2, $lineas['barras'][0]['cantidad']);

        // Contravidrios: 956 mm (1000 - 44) por el descuento de HA 62.
        $this->assertSame(956.0, (float) $lineas['barras'][2]['largoMM']);
        $this->assertSame(2, $lineas['barras'][2]['cantidad']);

        // Un vidrio de 1,000 × 1,000 m.
        $this->assertCount(1, $lineas['vidrios']);
        $this->assertEqualsWithDelta(1.0, $lineas['vidrios'][0]['anchoM'], 0.0001);

        // 4 barras de marco (4,072 kg) + 4 contravidrios (0,979 kg) = 5,051 kg
        // × 1,08 × 7,48 × 1,8 ≈ 73,4 → 73 ; vidrio max(12,3 × 2, 8) = 24,6 → 25
        $this->assertSame(73, $resultado->desglose['mh']);
        $this->assertSame(25, $resultado->desglose['vidrio']);
        $this->assertSame(98, $resultado->precio);
        $this->assertSame(98, (int) $abertura->precioUnitario);
        $this->assertNotNull($abertura->calculadoEn);
    }

    public function test_la_abertura_se_puede_cargar_desde_la_obra(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);

        $obra = Obra::query()->create([
            'numero' => 'T-2',
            'nombre' => 'Obra web',
            'fechaCreacion' => now()->toDateString(),
        ]);

        $this->actingAs($usuario)
            ->post("/obras/{$obra->id}/aberturas", [
                'tipo' => 'ABERTURA_SIMPLE',
                'idTipologia' => 6,
                'idSerie' => 10,
                'cantidad' => 2,
                'ancho' => 1200,
                'alto' => 900,
                'ubicacion' => 'Cocina',
                'terminacion' => 'PINTADO_BLANCO',
                'perfilMarco' => 'ADR 1470',
                'perfilContravidrio' => 'ADR 916',
                'vidrioId' => 1,
            ])
            ->assertRedirect();

        $abertura = AberturaSimple::query()->firstOrFail();

        $this->assertSame(2, (int) $abertura->cantidad);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
        $this->assertSame('Cocina', $abertura->ubicacion);

        $this->actingAs($usuario)
            ->get("/obras/{$obra->id}")
            ->assertOk()
            ->assertSee('Agregar abertura')
            ->assertSee('HA 62');
    }

    public function test_exporta_el_presupuesto_en_csv(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'perfilMarco' => 'ADR 1470',
            'perfilContravidrio' => 'ADR 916',
            'vidrioId' => 1,
        ]);

        $obra = Obra::query()->firstOrFail();

        $respuesta = $this->actingAs($usuario)->get("/obras/{$obra->id}/presupuesto.csv");

        $respuesta->assertOk();
        $respuesta->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $contenido = $respuesta->streamedContent();

        $this->assertStringContainsString('Tipología', $contenido);
        $this->assertStringContainsString('TOTAL', $contenido);
        $this->assertStringContainsString('98.00', $contenido);
    }

    public function test_exporta_el_presupuesto_en_xlsx(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'perfilMarco' => 'ADR 1470',
            'perfilContravidrio' => 'ADR 916',
            'vidrioId' => 1,
        ]);

        $obra = Obra::query()->firstOrFail();
        $respuesta = $this->actingAs($usuario)->get("/obras/{$obra->id}/presupuesto.xlsx");

        $respuesta->assertOk();

        $contenido = $respuesta->streamedContent();
        $this->assertStringStartsWith('PK', $contenido, 'El XLSX debe ser un ZIP');

        $ruta = tempnam(sys_get_temp_dir(), 'xlsx-test');
        file_put_contents($ruta, $contenido);

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($ruta) === true);

        $hoja = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($ruta);

        $this->assertStringContainsString('TOTAL', $hoja);
        $this->assertStringContainsString('98', $hoja);
    }

    public function test_exporta_el_presupuesto_en_pdf(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Tester', 'activo' => true]);
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'perfilMarco' => 'ADR 1470',
            'perfilContravidrio' => 'ADR 916',
            'vidrioId' => 1,
        ]);

        $obra = Obra::query()->firstOrFail();
        $respuesta = $this->actingAs($usuario)->get("/obras/{$obra->id}/presupuesto.pdf");

        $respuesta->assertOk();
        $respuesta->assertHeader('content-type', 'application/pdf');

        $contenido = $respuesta->getContent();
        $this->assertStringStartsWith('%PDF-', $contenido);
        $this->assertGreaterThan(1000, strlen($contenido));
    }

    public function test_falla_con_mensaje_claro_si_la_serie_no_tiene_constantes(): void
    {
        $abertura = $this->abertura();
        $abertura->forceFill(['idSerie' => 44])->save();

        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, ['perfilMarco' => 'ADR 1470']);
    }
}
