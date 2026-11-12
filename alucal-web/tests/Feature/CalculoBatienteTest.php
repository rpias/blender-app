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

class CalculoBatienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $perfiles = [
            '6210' => 0.789, '6211' => 0.848, '6234' => 0.888, '6223' => 0.921,
            '6212' => 0.316, '6225' => 0.257, '6226' => 0.211, '6217' => 0.294,
            '6257' => 0.24, '6232' => 0.194, '6230' => 0.213, '6231' => 0.213,
        ];

        foreach ($perfiles as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        $accesorios = [
            '230420' => 40, '230177' => 90, '230178' => 88, '230049' => 20, '230630' => 15,
            '230627' => 12, '230632' => 10, '100169' => 9, '230625' => 11, '230624' => 13,
            '100113' => 14, '230184' => 120, '230183' => 118, '230306' => 95, '230134' => 93,
            '230244' => 60, '230081' => 45, '230549' => 35, '230100' => 25, '230089' => 8,
        ];

        foreach ($accesorios as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 2, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(int $cantidad = 1): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'B-1', 'nombre' => 'Batiente', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => $cantidad, 'ancho' => 800, 'alto' => 2000, 'idTipologia' => 2, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Baño', 'tipo' => 'BATIENTE', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_batiente_de_una_hoja_recta(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'cantHojas' => 1,
            'hoja' => 'RECTA',
            'contravidrios' => 'RECTOS',
            'cierre' => 'FALLEBA',
            'colorHerrajes' => 'BLANCO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras'])->keyBy(fn ($b) => $b['perfil'].'|'.$b['largoMM']);

        // Marco 6210 a medida nominal.
        $this->assertTrue($barras->has('6210|800'));
        $this->assertTrue($barras->has('6210|2000'));

        // Hoja 6211: ancho − 39 y alto − 39.
        $this->assertTrue($barras->has('6211|761'));
        $this->assertTrue($barras->has('6211|1961'));

        // Contravidrio recto simple 6212.
        $this->assertTrue($barras->has('6212|761'));

        // Vidrio (1961 − 92) × (761 − 92) mm.
        $vidrio = $abertura->lineas['vidrios'][0];
        $this->assertEqualsWithDelta(0.669, $vidrio['anchoM'], 0.001);
        $this->assertEqualsWithDelta(1.869, $vidrio['altoM'], 0.001);
        $this->assertSame(1, $vidrio['cantidad']);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_batiente_de_dos_hojas_curva_con_dvh(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'cantHojas' => 2,
            'hoja' => 'CURVA',
            'contravidrios' => 'CURVOS',
            'cierre' => 'ALDABILLA',
            'colorHerrajes' => 'NEGRO',
            'espesorVidrio' => 14,
            'vidrioDvh' => ['ext' => 1, 'camara' => 12, 'int' => 1],
        ]);

        $abertura->refresh();
        $perfiles = collect($abertura->lineas['barras'])->pluck('perfil')->unique()->values()->all();

        // Hoja curva 6234, encuentro central 6223 y contravidrio curvo con DVH 6231.
        $this->assertContains('6234', $perfiles);
        $this->assertContains('6223', $perfiles);
        $this->assertContains('6231', $perfiles);

        // Dos paños de vidrio.
        $this->assertSame(2, $abertura->lineas['vidrios'][0]['cantidad']);
        $this->assertSame('dvh', $abertura->lineas['vidrios'][0]['tipo']);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_cantidad_multiplica_las_piezas(): void
    {
        $abertura = $this->abertura(3);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'cantHojas' => 1,
            'hoja' => 'RECTA',
            'contravidrios' => 'RECTOS',
            'cierre' => 'FALLEBA',
            'colorHerrajes' => 'NEGRO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();

        $this->assertSame(3, $abertura->lineas['vidrios'][0]['cantidad']);

        $marco = collect($abertura->lineas['barras'])->firstWhere('perfil', '6210');
        $this->assertSame(6.0, (float) $marco['cantidad']);
    }

    public function test_hoja_invalida_falla(): void
    {
        $abertura = $this->abertura();

        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'cantHojas' => 1,
            'hoja' => 'TRAPEZOIDAL',
            'contravidrios' => 'RECTOS',
            'cierre' => 'FALLEBA',
            'colorHerrajes' => 'BLANCO',
            'vidrioId' => 1,
        ]);
    }
}
