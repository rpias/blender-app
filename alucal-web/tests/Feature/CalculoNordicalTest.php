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

class CalculoNordicalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $perfiles = [
            'ADR 4251' => 1.1, 'ADR 4252' => 0.9, 'ADR 3913' => 1.0, 'ADR 4253' => 1.2,
            'ADR 3942' => 0.6, 'ADR 3943' => 0.55, 'ADR 3944' => 0.5, 'ADR 3945' => 0.45,
            'ADR 4254' => 0.3,
        ];

        foreach ($perfiles as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        $accesorios = [
            '230049' => 20, '230087' => 15, '230361' => 45, 'AA710' => 30, '230290' => 12,
            '230379' => 25, '230820' => 18, 'AA708' => 22, 'AA711' => 26, 'AA498' => 19,
            'AA213' => 8, 'AA709' => 40,
        ];

        foreach ($accesorios as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 23, 'idSerie' => 44, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 44, 'nombre' => 'Nórdical', 'abreviacion' => 'NOR', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'N-1', 'nombre' => 'Nórdical', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1800, 'alto' => 1400, 'idTipologia' => 23, 'idSerie' => 44,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Estar', 'tipo' => 'CORREDIZA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_una_hoja_con_pano_fijo(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'cantHojasCorredizas' => 1,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        // Ubral y dintel a ancho − 48; jamba de paño fijo 4253.
        $this->assertSame(1752.0, (float) $barras->firstWhere('perfil', 'ADR 4251')['largoMM']);
        $this->assertNotNull($barras->firstWhere('perfil', 'ADR 4253'));

        // Parantes 3942 y 3944 a alto − 98, 3943 a alto − 76.
        $this->assertSame(1302.0, (float) $barras->firstWhere('perfil', 'ADR 3942')['largoMM']);
        $this->assertSame(1324.0, (float) $barras->firstWhere('perfil', 'ADR 3943')['largoMM']);

        // Vidrio de hoja y de paño fijo.
        $this->assertCount(2, $abertura->lineas['vidrios']);
        $this->assertSame(1, $abertura->lineas['vidrios'][1]['cantidad']);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_dos_hojas_no_llevan_jamba_de_pano_fijo(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'cantHojasCorredizas' => 2,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        $this->assertNull($barras->firstWhere('perfil', 'ADR 4253'));
        $this->assertSame(4.0, (float) $barras->firstWhere('perfil', 'ADR 3945')['cantidad']);
        $this->assertSame(2, $abertura->lineas['vidrios'][0]['cantidad']);
    }
}
