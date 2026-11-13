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

class CalculoMosquiteroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['9500' => 1.115, '9525' => 0.751, '9520' => 0.845, '9530' => 0.757,
            '9580' => 0.218, '214' => 0.35, '6255' => 0.33] as $numero => $peso) {
            Perfil::query()->create(['Numero' => $numero, 'Peso' => $peso, 'Descripcion' => 'PERFIL '.$numero]);
        }

        foreach (['230420' => 40, '230049' => 20, '230087' => 15, '193155' => 25, '231001' => 53,
            '230637' => 9, '230829' => 8, '191280' => 30, '230661' => 12, '230428' => 11, '230623' => 6,
            '196150' => 7, '230861' => 16, '230862' => 18, '230863' => 20, '230864' => 22,
            '230865' => 24, '232001' => 30] as $codigo => $precio) {
            Accesorio::query()->create([
                'codigo' => $codigo, 'precio' => $precio, 'cantPrecio' => 1, 'descripcion' => 'ACC '.$codigo,
                'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
                'reposicion' => 0, 'cantHorasColocacion' => 0,
            ]);
        }

        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 3, 'idSerie' => 2, 'MargenMH' => 1.8, 'MargenMosq' => 2.6,
            'DespMH' => 1.08, 'DespMosq' => 1.0, 'CoefAjusteObraParticular' => 1.15,
        ]);
        PreciosPorKilo::query()->create(['terminacion' => 'PINTADO_BLANCO', 'rpt' => false, 'valor' => 7.48]);
        TipoVidrio::query()->create(['id' => 1, 'tipo' => 'FLOAT CLEAR']);
        VidrioSinCamara::query()->create(['id' => 1, 'idTipo' => 1, 'precio' => 12.3, 'precioMinimo' => 8.0, 'margen' => 2.0]);
        Serie::query()->create(['id' => 2, 'nombre' => 'Domina', 'abreviacion' => 'DOM', 'rpt' => false, 'activa' => true]);
    }

    private function abertura(): AberturaSimple
    {
        $obra = Obra::query()->create([
            'numero' => 'M-'.uniqid(), 'nombre' => 'Mosquitero', 'fechaCreacion' => now()->toDateString(),
        ]);
        $elemento = ElementoObra::query()->create(['tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1]);

        return AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1500, 'alto' => 1000, 'idTipologia' => 3, 'idSerie' => 2,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => 'BLANCO', 'colorPlasticos' => '',
            'ubicacion' => 'Living', 'tipo' => 'CORREDIZA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
        ]);
    }

    private function precio(bool $conMosquitero): int
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'enganche' => 'SIMPLE',
            'mosquitero' => $conMosquitero,
            'vidrioId' => 1,
        ]);

        return (int) $abertura->fresh()->precioUnitario;
    }

    public function test_el_mosquitero_agrega_perfiles_y_accesorios(): void
    {
        $abertura = $this->abertura();

        app(CalcularAberturaSimple::class)->ejecutar($abertura, [
            'enganche' => 'SIMPLE',
            'mosquitero' => true,
            'vidrioId' => 1,
        ]);

        $lineas = $abertura->fresh()->lineas;
        $mosquitero = collect($lineas['barras'])->where('parte', 'MOSQUITERO');

        // 2 horizontales de ancho − 5 y 2 verticales de alto − 5.
        $this->assertCount(2, $mosquitero);
        $this->assertSame(1495.0, (float) $mosquitero->firstWhere('largoMM', 1495)['largoMM']);

        $accesorios = collect($lineas['accesorios'])->where('parte', 'MOSQUITERO')->pluck('codigo')->all();
        $this->assertContains('230428', $accesorios);
        $this->assertContains('230863', $accesorios);
    }

    public function test_el_mosquitero_aumenta_el_precio(): void
    {
        $sin = $this->precio(false);
        $con = $this->precio(true);

        $this->assertGreaterThan($sin, $con);
    }
}
