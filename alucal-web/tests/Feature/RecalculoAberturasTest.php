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
use App\Models\VidrioSinCamara;
use App\Services\Aberturas\MapeadorParametros;
use Database\Seeders\ConstantesCalculoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecalculoAberturasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConstantesCalculoSeeder::class);

        Perfil::query()->create(['Numero' => 'ADR 1470', 'Peso' => 1.018, 'Descripcion' => 'MARCO BATIENTE']);
        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 6, 'idSerie' => 10, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'ANODIZADO_NATURAL', 'rpt' => false, 'valor' => 6.0]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 10, 'nombre' => 'HA 62', 'abreviacion' => 'HA62', 'rpt' => false, 'activa' => true]);

        foreach ([[25, 'Marco', 'PERFIL'], [135, 'Definir_Contravidrios_Manualmente', 'CONTRAVIDRIOS'], [24, 'Divisores Marco Fijo', 'DIVISORES'], [5, 'AcristalamientoEnteroSinOpcionTablilla', 'ACRISTALAMIENTO']] as [$id, $nombre, $tipo]) {
            DB::table('definicion_parametro')->insert(['id' => $id, 'nombre' => $nombre, 'tipoDeDato' => $tipo]);
        }
    }

    private function aberturaConParametros(): AberturaSimple
    {
        $obra = Obra::query()->create(['numero' => 'R-1', 'nombre' => 'Recalculo', 'fechaCreacion' => now()->toDateString()]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        $abertura = AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 500, 'idTipologia' => 6, 'idSerie' => 10,
            'terminacion' => 'ANODIZADO_NATURAL', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => 'Baño', 'tipo' => 'FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);

        foreach ([[1, 25], [2, 135], [3, 24], [4, 5]] as [$id, $def]) {
            DB::table('valor_parametro_particular')->insert([
                'id' => $id, 'idAberturaSimple' => $abertura->id, 'idParamPorDefecto' => -1,
                'idCorreccionDescuentos' => -1, 'idOrdenVerificada' => -1, 'idDefParamParticular' => $def,
            ]);
        }

        DB::table('valor_parametro_particular_perfil')->insert(['id' => 1, 'codigoPerfil' => 'ADR 1470']);
        DB::table('valor_parametro_particular_contravidrios')->insert([
            'id' => 2, 'contravidriosDefinidos' => 0, 'contravidrioHorizontal' => '', 'contravidrioVertical' => '',
        ]);
        DB::table('valor_parametro_particular_divisores')->insert([
            'id' => 3, 'cantDivisoresHorizontales' => 0, 'divisorHorizontal' => null,
            'cantDivisoresVerticales' => 0, 'divisorVertical' => null,
        ]);
        DB::table('valor_parametro_particular_acristalamiento')->insert([
            'id' => 4, 'tipoAcristalamiento' => 'ACRISTALAMIENTO_ENTERO', 'materialAcristalamientoEntero' => 'VIDRIO',
            'materialAcristalamientoSuperior' => '', 'materialAcristalamientoInferior' => '',
            'cristalEntero' => 'VIDRIOSINCAMARA@1', 'cristalSuperior' => '', 'cristalInferior' => '',
            'divisor' => null, 'tablillaEnteraVertical' => null, 'tablillaSuperiorVertical' => null,
            'tablillaInferiorVertical' => null, 'dimensionesColonialEntero' => '',
            'dimensionesColonialSuperior' => '', 'dimensionesColonialInferior' => '',
            'tipoAcristalamiento2' => '', 'materialAcristalamientoEntero2' => '',
            'materialAcristalamientoSuperior2' => '', 'materialAcristalamientoInferior2' => '',
            'cristalEntero2' => '', 'cristalSuperior2' => '', 'cristalInferior2' => '',
            'divisor2' => null, 'tablillaEnteraVertical2' => null, 'tablillaSuperiorVertical2' => null,
            'tablillaInferiorVertical2' => null, 'dimensionesColonialEntero2' => '',
            'dimensionesColonialSuperior2' => '', 'dimensionesColonialInferior2' => '',
        ]);

        return $abertura;
    }

    public function test_mapea_los_parametros_guardados_por_el_java(): void
    {
        $abertura = $this->aberturaConParametros();

        $parametros = app(MapeadorParametros::class)->para($abertura);

        $this->assertSame('ADR 1470', $parametros['perfilMarco']);
        $this->assertNull($parametros['perfilContravidrio']);
        $this->assertSame(0, $parametros['divisoresVerticales']);
        $this->assertSame(1, $parametros['vidrioId']);
    }

    public function test_el_comando_recalcula_y_guarda_el_precio(): void
    {
        $abertura = $this->aberturaConParametros();

        $this->artisan('alucal:recalcular')
            ->expectsOutputToContain('Recalculadas: 1')
            ->assertSuccessful();

        $abertura->refresh();

        $this->assertGreaterThan(0, (int) $abertura->precioUnitario);
        $this->assertNotNull($abertura->lineas);
        $this->assertNotNull($abertura->calculadoEn);
    }
}
