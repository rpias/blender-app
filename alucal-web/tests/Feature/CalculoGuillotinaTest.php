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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculoGuillotinaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['204' => 0.72, '205' => 0.65, '201' => 0.44, '202' => 0.52, '216' => 0.38] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach ([
            '230478' => 40, '230447' => 18, '230066' => 60, '230067' => 58, '100203' => 12,
            '230460' => 9, '230941' => 6, '100104' => 8,
        ] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 4, 'idSerie' => 19, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 19, 'nombre' => 'Serie 20', 'abreviacion' => 'S20', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'G-1', 'nombre' => 'Guillotina', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1200, 'alto' => 1000, 'idTipologia' => 4, 'idSerie' => 19,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'GRIS', 'colorPlasticos' => '',
            'ubicacion' => 'Cocina', 'tipo' => 'GUILLOTINA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_guillotina_automatica(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'punzonadoManual' => false,
            'colorHerrajes' => 'GRIS',
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        // Marco lateral a alto − 22, horizontal a ancho.
        $this->assertSame(978.0, (float) $barras->firstWhere('perfil', '204')['largoMM']);
        $this->assertSame(1200.0, (float) $barras->firstWhere('perfil', '205')['largoMM']);

        // Hoja automática: alto/2 − 9 = 491.
        $this->assertSame(491.0, (float) $barras->firstWhere('perfil', '201')['largoMM']);

        // Dos vidrios de (alto/2 − 0,057) × (ancho − 0,112).
        $this->assertSame(2, $abertura->lineas['vidrios'][0]['cantidad']);
        $this->assertEqualsWithDelta(1.088, $abertura->lineas['vidrios'][0]['anchoM'], 0.001);
        $this->assertEqualsWithDelta(0.443, $abertura->lineas['vidrios'][0]['altoM'], 0.001);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_guillotina_manual_alarga_distinto_y_suma_accesorio(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'punzonadoManual' => true,
            'colorHerrajes' => 'NEGRO',
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);
        $accesorios = collect($abertura->lineas['accesorios']);

        // Manual: alto/2 − 74 = 426 y se agrega 230460.
        $this->assertSame(426.0, (float) $barras->firstWhere('perfil', '201')['largoMM']);
        $this->assertNotNull($accesorios->firstWhere('codigo', '230460'));
        $this->assertNotNull($accesorios->firstWhere('codigo', '230066'));
    }
}
