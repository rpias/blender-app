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

class CalculoTabaqueraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ADR 1470' => 1.02, 'ADR 1471' => 0.78, 'ADR 1655' => 0.3] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach ([
            '230049' => 20, '230419' => 40, '230376' => 28, '230716' => 12, 'AA258' => 22,
            '230353' => 18, '230728' => 15, '230281' => 55, '230282' => 53, 'H-87' => 30,
            '100460' => 70, '100462' => 65, '193760' => 9, '230711' => 7,
        ] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 12, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'T-1', 'nombre' => 'Tabaquera', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 700, 'alto' => 500, 'idTipologia' => 12, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Cocina', 'tipo' => 'TABAQUERA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_tabaquera_con_sapito(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'contravidrios' => 'RECTOS',
            'cierre' => 'SAPITO',
            'colorHerrajes' => 'BLANCO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);
        $accesorios = collect($abertura->lineas['accesorios']);

        $this->assertSame(700.0, (float) $barras->firstWhere('perfil', 'ADR 1470')['largoMM']);
        $this->assertSame(648.0, (float) $barras->firstWhere('perfil', 'ADR 1471')['largoMM']);
        // Contravidrio vertical: alto − 191 ; horizontal: ancho − 153.
        $this->assertSame(309.0, (float) $barras->where('perfil', 'ADR 1655')->last()['largoMM']);
        $this->assertSame(547.0, (float) $barras->where('perfil', 'ADR 1655')->first()['largoMM']);

        // Sapito blanco + limitadores.
        $this->assertNotNull($accesorios->firstWhere('codigo', '230282'));
        $this->assertNotNull($accesorios->firstWhere('codigo', 'H-87'));

        $this->assertEqualsWithDelta(0.331, $abertura->lineas['vidrios'][0]['altoM'], 0.001);
        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_cierre_invalido_falla(): void
    {
        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($this->abertura(), [
            'cierre' => 'INVENTADO',
            'vidrioId' => 1,
        ]);
    }
}
