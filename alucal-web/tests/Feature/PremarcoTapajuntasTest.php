<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\Accesorio;
use App\Models\ElementoObra;
use App\Models\MargenesDesperdiciosCoefAjuste;
use App\Models\Obra;
use App\Models\Perfil;
use App\Models\PrecioMetroLinealPremarco;
use App\Models\PrecioMetroLinealTapajuntas;
use App\Models\PreciosPorKilo;
use App\Models\Serie;
use App\Models\TipoVidrio;
use App\Models\User;
use App\Models\VidrioSinCamara;
use App\Services\Aberturas\CalcularAberturaSimple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremarcoTapajuntasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Perfil::query()->create(['Numero' => 'ADR 1470', 'Peso' => 1.0, 'Descripcion' => 'MARCO']);
        Perfil::query()->create(['Numero' => 'ADR 1471', 'Peso' => 0.8, 'Descripcion' => 'HOJA']);
        Perfil::query()->create(['Numero' => 'ADR 1655', 'Peso' => 0.3, 'Descripcion' => 'CONTRA']);
        PrecioMetroLinealPremarco::query()->create(['perfil' => '4570', 'precio' => 10.0]);
        PrecioMetroLinealTapajuntas::query()->create(['perfil' => 'ADR 62', 'precio' => 5.0]);

        foreach (['230049' => 20, '230419' => 40, '230376' => 28, '230716' => 12, 'AA258' => 22,
            '230353' => 18, '230728' => 15, '230281' => 55, '230282' => 53, 'H-87' => 30, '100460' => 70, '100462' => 65, '193760' => 9, '230711' => 7] as $codigo => $precio) {
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
        $obra = Obra::query()->create([
            'numero' => 'PT-'.uniqid(), 'nombre' => 'Premarco', 'fechaCreacion' => now()->toDateString(),
        ]);
        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1,
        ]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 12, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'TABAQUERA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    private function calcular(array $extra): int
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, array_merge([
            'contravidrios' => 'RECTOS',
            'cierre' => 'SAPITO',
            'colorHerrajes' => 'BLANCO',
            'espesorVidrio' => 4,
            'vidrioId' => 1,
        ], $extra));

        return (int) $abertura->fresh()->precioUnitario;
    }

    public function test_el_premarco_aumenta_el_precio(): void
    {
        $sin = $this->calcular([]);
        $con = $this->calcular(['premarcoCodigo' => '4570', 'premarcoAbajo' => true]);

        // 2×(1,0+1,0) m = 4 m × 10 = 40
        $this->assertSame(40, $con - $sin);
    }

    public function test_sin_el_lado_de_abajo_el_premarco_cuesta_menos(): void
    {
        $conAbajo = $this->calcular(['premarcoCodigo' => '4570', 'premarcoAbajo' => true]);
        $sinAbajo = $this->calcular(['premarcoCodigo' => '4570', 'premarcoAbajo' => false]);

        // 4 m vs 3 m: 40 contra 30
        $this->assertSame(30, $sinAbajo - $this->calcular([]));
        $this->assertSame(10, $conAbajo - $sinAbajo);
    }

    public function test_el_tapajuntas_se_suma_al_premarco(): void
    {
        $sin = $this->calcular([]);
        $ambos = $this->calcular([
            'premarcoCodigo' => '4570',
            'premarcoAbajo' => true,
            'tapajuntasCodigo' => 'ADR 62',
            'tapajuntasAbajo' => true,
        ]);

        // 40 de premarco + 20 de tapajuntas (4 m × 5)
        $this->assertSame(60, $ambos - $sin);
    }

    public function test_guarda_la_eleccion_en_los_parametros(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'premarcoCodigo' => '4570',
            'premarcoAbajo' => false,
            'tapajuntasCodigo' => 'ADR 62',
            'tapajuntasAbajo' => true,
            'vidrioId' => 1,
        ]);

        $parametros = $abertura->fresh()->parametros;
        $this->assertSame('4570', $parametros['premarcoCodigo']);
        $this->assertFalse((bool) $parametros['premarcoAbajo']);
        $this->assertSame('ADR 62', $parametros['tapajuntasCodigo']);
    }

    public function test_rechaza_un_codigo_de_premarco_inexistente(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
        $obra = Obra::query()->create([
            'numero' => 'PT-X', 'nombre' => 'Obra', 'fechaCreacion' => now()->toDateString(),
        ]);

        $this->actingAs($usuario)
            ->post("/obras/{$obra->id}/aberturas", [
                'tipo' => 'ABERTURA_SIMPLE',
                'variante' => '12:10',
                'ancho' => 700,
                'alto' => 500,
                'cantidad' => 1,
                'terminacion' => 'PINTADO_BLANCO',
                'vidrioId' => 1,
                'premarcoCodigo' => 'NO-EXISTE',
            ])
            ->assertSessionHasErrors('premarcoCodigo');
    }
}
