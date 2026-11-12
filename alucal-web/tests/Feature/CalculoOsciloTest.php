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

class CalculoOsciloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ADR 1470' => 1.02, 'ADR 1483' => 1.05, 'ADR 1471' => 0.78, 'ADR 1655' => 0.3] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach ([
            '230049' => 20, '230419' => 40, '230376' => 28, '230716' => 12, '230353' => 18,
            '230728' => 15, '193760' => 9, '230711' => 7,
            '230671' => 300, '230672' => 305, '230673' => 320, '230674' => 325,
            '230675' => 340, '230676' => 345, '230741' => 360, '230742' => 365,
            '230677' => 380, '230678' => 385, '230679' => 400, '230680' => 405,
            '230681' => 420, '230682' => 425,
        ] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 7, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(float $ancho = 700, float $alto = 1200): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'O-1', 'nombre' => 'Oscilo', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => $ancho, 'alto' => $alto, 'idTipologia' => 7, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Living', 'tipo' => 'OSCILOBATIENTE', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_coplanar_elige_kit_por_ancho_de_hoja(): void
    {
        $abertura = $this->abertura(700, 1200);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marco' => 'ADR 1470',
            'contravidrios' => 'RECTOS',
            'colorHerrajes' => 'BLANCO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $accesorios = collect($abertura->lineas['accesorios']);

        // Hoja de 648 mm: kit coplanar blanco para 580-1.700 mm → 230682.
        $this->assertNotNull($accesorios->firstWhere('codigo', '230682'));
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_escalonado_usa_la_otra_tabla_de_kits(): void
    {
        $abertura = $this->abertura(700, 1200);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marco' => 'ADR 1483',
            'contravidrios' => 'RECTOS',
            'colorHerrajes' => 'NEGRO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $accesorios = collect($abertura->lineas['accesorios']);

        // Hoja de 648 mm, negro, escalonado: kit 230675.
        $this->assertNotNull($accesorios->firstWhere('codigo', '230675'));
    }

    public function test_hoja_demasiado_baja_no_tiene_kit(): void
    {
        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($this->abertura(400, 1000), [
            'marco' => 'ADR 1470',
            'vidrioId' => 1,
        ]);
    }
}
