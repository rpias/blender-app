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
use App\Models\VidrioSinCamara;
use App\Services\Aberturas\CalcularAberturaSimple;
use App\Services\Aberturas\SerieSinConstantes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculoProyDeslizTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ADR 1483' => 1.05, 'ADR 1484' => 0.82, 'ADR 1655' => 0.3] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach ([
            '230049' => 20, '230415' => 35, '230376' => 28, '193780' => 14, '230306' => 45,
            '230134' => 43, '192804' => 22, '193285' => 18, '230353' => 18, '193790' => 16,
            '193760' => 9, '230711' => 7,
        ] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 9, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(float $ancho = 800, float $alto = 1000): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'PD-'.uniqid(), 'nombre' => 'ProyDesliz', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => $ancho, 'alto' => $alto, 'idTipologia' => 9, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Baño', 'tipo' => 'PROYECCION_Y_DESLIZ', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_calcula_marco_hoja_y_vidrio(): void
    {
        $abertura = $this->abertura(800, 1000);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'contravidrios' => 'RECTOS',
            'colorHerrajes' => 'BLANCO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        $this->assertSame(800.0, (float) $barras->firstWhere('perfil', 'ADR 1483')['largoMM']);
        $this->assertSame(754.0, (float) $barras->firstWhere('perfil', 'ADR 1484')['largoMM']);
        $this->assertSame(636.0, (float) $barras->firstWhere('perfil', 'ADR 1655')['largoMM']);

        $accesorios = collect($abertura->lineas['accesorios'])->pluck('codigo')->all();
        $this->assertContains('230134', $accesorios); // H91 blanco
        $this->assertContains('193780', $accesorios);

        $this->assertEqualsWithDelta(0.62, $abertura->lineas['vidrios'][0]['anchoM'], 0.001);
        $this->assertEqualsWithDelta(0.82, $abertura->lineas['vidrios'][0]['altoM'], 0.001);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_usa_la_h91_negra_si_corresponde(): void
    {
        $abertura = $this->abertura(800, 1000);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'colorHerrajes' => 'NEGRO',
            'vidrioId' => 1,
        ]);

        $accesorios = collect($abertura->refresh()->lineas['accesorios'])->pluck('codigo')->all();

        $this->assertContains('230306', $accesorios);
        $this->assertNotContains('230134', $accesorios);
    }

    public function test_contravidrios_curvos_con_vidrio_fino_falla(): void
    {
        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($this->abertura(), [
            'contravidrios' => 'CURVOS',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);
    }
}
