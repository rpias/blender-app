<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\Accesorio;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\Perfil;
use App\Services\Aberturas\CortesPerimetrales;
use App\Servicios\Exportacion\ComputoMateriales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CortesPerimetralesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Perfil::query()->create(['Numero' => '4570', 'Peso' => 0.5, 'Descripcion' => 'PREMARCO 4570']);
        Perfil::query()->create(['Numero' => 'ADR 62', 'Peso' => 0.2, 'Descripcion' => 'TAPAJUNTAS 62']);
        Accesorio::query()->create([
            'codigo' => '230736', 'precio' => 30, 'cantPrecio' => 1, 'descripcion' => 'ESCUADRA 230736',
            'idCategoria' => 1, 'perteneceAListadoAccImportados' => false, 'stockMinimo' => 0,
            'reposicion' => 0, 'cantHorasColocacion' => 0,
        ]);
    }

    public function test_premarco_con_abajo_lleva_cuatro_escuadras(): void
    {
        $resultado = app(CortesPerimetrales::class)->premarco('4570', 1000, 1000, true);

        $this->assertSame(1032.0, $resultado['barras'][0]['largoMM']);
        $this->assertSame(2, $resultado['barras'][0]['cantidad']);
        $this->assertSame(1032.0, $resultado['barras'][1]['largoMM']);
        $this->assertSame('230736', $resultado['escuadras']['codigo']);
        $this->assertSame(4, $resultado['escuadras']['cantidad']);
    }

    public function test_premarco_sin_abajo_lleva_dos_escuadras_y_jamba_mas_corta(): void
    {
        $resultado = app(CortesPerimetrales::class)->premarco('4570', 1000, 1000, false);

        $this->assertSame(1, $resultado['barras'][0]['cantidad']);
        $this->assertSame(1016.0, $resultado['barras'][1]['largoMM']);
        $this->assertSame(2, $resultado['escuadras']['cantidad']);
    }

    public function test_el_premarco_2195_no_lleva_escuadras(): void
    {
        $resultado = app(CortesPerimetrales::class)->premarco('2195', 1000, 1000, true);

        $this->assertNull($resultado['escuadras']);
        $this->assertSame(1035.0, $resultado['barras'][0]['largoMM']);
    }

    public function test_tapajuntas_descuenta_segun_el_codigo(): void
    {
        $resultado = app(CortesPerimetrales::class)->tapajuntas('ADR 62', 1000, 1000, true);

        $this->assertSame(1066.0, $resultado['barras'][0]['largoMM']);
        $this->assertSame(2, $resultado['barras'][0]['cantidad']);
    }

    public function test_el_computo_incluye_los_cortes_del_premarco(): void
    {
        $obra = Obra::query()->create([
            'numero' => 'CP-1', 'nombre' => 'Cortes', 'fechaCreacion' => now()->toDateString(),
        ]);
        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1,
        ]);

        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 12, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'TABAQUERA', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => 100,
            'lineas' => ['barras' => [], 'accesorios' => [], 'vidrios' => [], 'kilos' => 0],
            'parametros' => [
                'premarcoCodigo' => '4570', 'premarcoAbajo' => true,
                'tapajuntasCodigo' => 'ADR 62', 'tapajuntasAbajo' => true,
            ],
        ]);

        $computo = app(ComputoMateriales::class)->para($obra->fresh());
        $codigos = collect($computo['perfiles'])->pluck('codigo')->all();

        $this->assertContains('4570', $codigos);
        $this->assertContains('ADR 62', $codigos);

        $escuadras = collect($computo['accesorios'])->firstWhere('codigo', '230736');
        $this->assertNotNull($escuadras);
        $this->assertSame(4.0, $escuadras['cantidad']);
    }
}
