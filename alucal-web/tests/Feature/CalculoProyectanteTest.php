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

class CalculoProyectanteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ADR 1483' => 1.05, 'ADR 1484' => 0.8, 'ADR 1655' => 0.3, 'ADR 1295' => 0.35] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach ([
            '230049' => 20, '230415' => 35, '230376' => 28, '193780' => 12, '230306' => 95,
            '230134' => 93, '192804' => 40, '193285' => 22, '230353' => 18, '193790' => 15,
            '193760' => 9, '230711' => 7,
        ] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 10, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'P-1', 'nombre' => 'Proyectante', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 600, 'alto' => 600, 'idTipologia' => 10, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Baño', 'tipo' => 'PROYECTANTE', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    public function test_proyectante_con_contravidrios_rectos(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'contravidrios' => 'RECTOS',
            'colorHerrajes' => 'BLANCO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ]);

        $abertura->refresh();
        $barras = collect($abertura->lineas['barras']);

        // Marco a medida nominal y hoja a −46.
        $this->assertSame(600.0, (float) $barras->firstWhere('perfil', 'ADR 1483')['largoMM']);
        $this->assertSame(554.0, (float) $barras->firstWhere('perfil', 'ADR 1484')['largoMM']);

        // Contravidrio 1655 con medidas propias (−164 / −202).
        $contra = collect($abertura->lineas['barras'])->where('perfil', 'ADR 1655')->pluck('largoMM')->all();
        $this->assertEqualsCanonicalizing([436.0, 398.0], array_map('floatval', $contra));

        // Vidrio único de 0,42 × 0,42 m.
        $this->assertSame(1, $abertura->lineas['vidrios'][0]['cantidad']);
        $this->assertEqualsWithDelta(0.42, $abertura->lineas['vidrios'][0]['anchoM'], 0.001);

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
    }

    public function test_curvos_exige_al_menos_14_mm(): void
    {
        $this->expectException(SerieSinConstantes::class);

        app(CalcularAberturaSimple::class)->ejecutar($this->abertura(), [
            'contravidrios' => 'CURVOS',
            'espesorVidrio' => 6,
            'vidrioId' => 1,
        ]);
    }
}
