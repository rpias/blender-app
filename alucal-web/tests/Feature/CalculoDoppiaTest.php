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

class CalculoDoppiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $perfiles = [
            'ADR 2471' => 1.1, 'ADR 2472' => 1.2, 'ADR 2473' => 0.5, 'ADR 2475' => 0.7,
            'ADR 2476' => 0.6, 'ADR 2477' => 0.9, 'ADR 910' => 0.4, 'ADR 1152' => 0.8,
            'ADR 1661' => 0.8, 'ADR 2494' => 1.0, 'ADR 2496' => 1.4, 'ADR 2474' => 0.7,
            'ADR 2479' => 0.9, 'ADR 2204' => 0.6, 'ADR 2209' => 0.8,
            'ADR 2478' => 1.0, 'ADR 2480' => 0.75, 'ADR 2495' => 1.1, 'ADR 2497' => 1.5,
        ];

        foreach ($perfiles as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        $accesorios = [
            '230049' => 20, '230087' => 15, '230313' => 30, '230314' => 32, 'AA806' => 40,
            '230347' => 18, 'AA805' => 22, '230030' => 90, '230558' => 88, '230586' => 50,
            '230581' => 45, '230470' => 25, '230471' => 24, '230317' => 12, '230637' => 9,
            '230632' => 10, '230625' => 11, '230827' => 8, '230828' => 9,
            '192510' => 60, '192515' => 62, '192535' => 35, 'AA808' => 70,
        ];

        foreach ($accesorios as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 14, 'idSerie' => 3, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 3, 'nombre' => 'Doppia', 'abreviacion' => 'DOP', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'D-1', 'nombre' => 'Doppia', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 2400, 'alto' => 1500, 'idTipologia' => 14, 'idSerie' => 3,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Estar', 'tipo' => 'CORREDIZA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_calcula_2_mas_1_con_marco_bajo(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marcoInferior' => 'ADR 2494',
            'marcoSuperior' => 'ADR 2494',
            'paranteLateral' => 'ADR 2474',
            'zocalo' => 'ADR 2204',
            'cabezal' => 'ADR 2204',
            'enganche' => 'SIMPLE',
            'colorHerrajes' => 'BLANCO',
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        // Marco inferior y superior a ancho − 48, jambas a alto nominal.
        $this->assertSame(2352.0, (float) $barras->firstWhere('perfil', 'ADR 2494')['largoMM']);
        $this->assertSame(1500.0, (float) $barras->firstWhere('perfil', 'ADR 2472')['largoMM']);

        // Refuerzo bajo ADR 1661 (marco 2494).
        $this->assertNotNull($barras->firstWhere('perfil', 'ADR 1661'));

        // Hoja móvil: alto − 70 = 1430.
        $this->assertSame(1430.0, (float) $barras->firstWhere('perfil', 'ADR 2473')['largoMM']);

        // Vidrios: 1 paño fijo + 2 móviles.
        $this->assertCount(2, $abertura->lineas['vidrios']);
        $this->assertSame(1, $abertura->lineas['vidrios'][0]['cantidad']);
        $this->assertSame(2, $abertura->lineas['vidrios'][1]['cantidad']);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_marco_alto_descuenta_mas_y_usa_refuerzo_1152(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marcoInferior' => 'ADR 2496',
            'marcoSuperior' => 'ADR 2496',
            'paranteLateral' => 'ADR 2479',
            'zocalo' => 'ADR 2209',
            'cabezal' => 'ADR 2209',
            'enganche' => 'DOBLEMENTE REFORZADO',
            'colorHerrajes' => 'NEGRO',
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        $this->assertNotNull($barras->firstWhere('perfil', 'ADR 1152'));
        // alto − (70 + 45 + 45) = 1340
        $this->assertSame(1340.0, (float) $barras->firstWhere('perfil', 'ADR 2479')['largoMM']);
    }

    public function test_calcula_2_mas_2(): void
    {
        $abertura = $this->abertura();
        $abertura->update(['idTipologia' => 15, 'ancho' => 3000]);

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'marcoInferior' => 'ADR 2494',
            'paranteLateral' => 'ADR 2474',
            'zocalo' => 'ADR 2204',
            'enganche' => 'SIMPLE',
            'colorHerrajes' => 'BLANCO',
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        // Dintel 2495 y jambas 2478 a alto nominal.
        $this->assertNotNull($barras->firstWhere('perfil', 'ADR 2495'));
        $this->assertSame(1500.0, (float) $barras->firstWhere('perfil', 'ADR 2478')['largoMM']);

        // Cuatro hojas móviles y seis parantes simples.
        $this->assertSame(4, $abertura->lineas['vidrios'][0]['cantidad']);
        $this->assertSame(6.0, (float) $barras->firstWhere('perfil', 'ADR 2474')['cantidad']);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_enganche_invalido_falla(): void
    {
        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($this->abertura(), [
            'enganche' => 'RARO',
            'vidrioId' => 1,
        ]);
    }
}
