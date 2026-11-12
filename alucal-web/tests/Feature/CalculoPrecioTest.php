<?php

namespace Tests\Feature;

use App\Models\CoeficientesDeCalculoDePrecio;
use App\Models\MargenesDesperdiciosCoefAjuste;
use App\Models\PrecioMetroLinealPremarco;
use App\Models\PreciosPorKilo;
use App\Services\Precios\AberturaParaPrecio;
use App\Services\Precios\CalculadoraPrecioAbertura;
use App\Services\Precios\Coeficientes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculoPrecioTest extends TestCase
{
    use RefreshDatabase;

    private function sembrarCoeficientes(): void
    {
        MargenesDesperdiciosCoefAjuste::query()->create([
            'idTipologia' => 3,
            'idSerie' => 2,
            'MargenMH' => 1.8,
            'MargenMosq' => 2.6,
            'DespMH' => 1.08,
            'DespMosq' => 1.0,
            'CoefAjusteObraParticular' => 1.15,
        ]);

        PreciosPorKilo::query()->create([
            'terminacion' => 'PINTADO_BLANCO',
            'rpt' => false,
            'valor' => 7.48,
        ]);

        PrecioMetroLinealPremarco::query()->create([
            'perfil' => '4570',
            'precio' => 11.9,
        ]);

        CoeficientesDeCalculoDePrecio::query()->create(['clave' => 'MARGEN_DVH_CARPINTERIA', 'valor' => '1.22']);
    }

    public function test_calcula_marco_vidrio_y_premarco_con_el_redondeo_original(): void
    {
        $this->sembrarCoeficientes();

        $abertura = new AberturaParaPrecio(
            anchoMM: 1000,
            altoMM: 1000,
            idTipologia: 3,
            idSerie: 2,
            terminacion: 'PINTADO_BLANCO',
            barras: [
                ['pesoKgPorMetro' => 2.0, 'cantidad' => 2, 'largoMM' => 1000, 'parte' => 'MARCO'],
            ],
            accesorios: [
                ['precioUnitario' => 100.0, 'cantidad' => 1.0, 'parte' => 'MARCO'],
            ],
            vidrios: [
                ['tipo' => 'simple', 'cantidad' => 1, 'anchoM' => 1.0, 'altoM' => 1.0, 'costoM2' => 20.0, 'precioMinimo' => 30.0, 'margen' => 2.0],
            ],
            premarco: ['codigo' => '4570', 'perimetroMM' => 3000],
        );

        $resultado = app(CalculadoraPrecioAbertura::class)->calcular($abertura);

        // (4 kg × 1.08 × 7.48 + 100) × 1.8 = 238.16 → 238
        $this->assertSame(238, $resultado->desglose['mh']);
        // max(1 m² × 20 × 2.0, 30) = 40
        $this->assertSame(40, $resultado->desglose['vidrio']);
        // 3 m × 11.9 = 35.7 → 36
        $this->assertSame(36, $resultado->desglose['premarcoTapajuntas']);
        $this->assertSame(238 + 40 + 36, $resultado->precio);
        $this->assertEqualsWithDelta(4.0, $resultado->kgTotales, 0.001);
    }

    public function test_el_vidrio_respeta_el_minimo_por_pieza(): void
    {
        $this->sembrarCoeficientes();

        $abertura = new AberturaParaPrecio(
            anchoMM: 300,
            altoMM: 300,
            idTipologia: 3,
            idSerie: 2,
            terminacion: 'PINTADO_BLANCO',
            vidrios: [
                ['tipo' => 'simple', 'cantidad' => 2, 'anchoM' => 0.3, 'altoM' => 0.3, 'costoM2' => 20.0, 'precioMinimo' => 30.0, 'margen' => 2.0],
            ],
        );

        $resultado = app(CalculadoraPrecioAbertura::class)->calcular($abertura);

        // 0.09 m² × 20 × 2 = 3.6 < 30 ⇒ 30 por pieza × 2 piezas
        $this->assertSame(60, $resultado->desglose['vidrio']);
    }

    public function test_aplica_moneda_iva_y_ajuste_en_ese_orden(): void
    {
        $calculadora = app(CalculadoraPrecioAbertura::class);

        // 100 × 40 (pesos) = 4000 → ×1.22 = 4880 → ×0.9 = 4392
        $this->assertSame(4392, $calculadora->conMonedaImpuestosYAjuste(
            precioBase: 100,
            enPesos: true,
            cotizacion: 40.0,
            agregarIva: true,
            coefAjuste: 0.9,
        ));
    }

    public function test_falla_si_no_hay_precio_de_premarco(): void
    {
        $this->sembrarCoeficientes();

        $abertura = new AberturaParaPrecio(
            anchoMM: 1000,
            altoMM: 1000,
            idTipologia: 3,
            idSerie: 2,
            terminacion: 'PINTADO_BLANCO',
            premarco: ['codigo' => 'NO_EXISTE', 'perimetroMM' => 3000],
        );

        $this->expectException(\RuntimeException::class);

        app(CalculadoraPrecioAbertura::class)->calcular($abertura);
    }

    public function test_usa_los_coeficientes_cargados_de_la_base(): void
    {
        $this->sembrarCoeficientes();

        $coeficientes = app(Coeficientes::class);

        $this->assertEqualsWithDelta(1.22, $coeficientes->coeficiente('MARGEN_DVH_CARPINTERIA'), 0.001);
        $this->assertEqualsWithDelta(1.8, $coeficientes->margenes(3, 2)['margenMH'], 0.001);
        $this->assertEqualsWithDelta(7.48, $coeficientes->precioPorKilo('PINTADO_BLANCO', false), 0.001);
    }
}
