<?php

namespace Tests\Feature;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\User;
use App\Support\Moneda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormatoMonedaTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatea_con_dos_decimales(): void
    {
        $this->assertSame('0,00', Moneda::formatear(0));
        $this->assertSame('1.234,56', Moneda::formatear(1234.56));
        $this->assertSame('98,00', Moneda::formatear(98));
        $this->assertSame('U$S 1.882,00', Moneda::conSimbolo(1882));
    }

    public function test_redondea_a_dos_decimales(): void
    {
        $this->assertSame('10,13', Moneda::formatear(10.125));
        $this->assertSame('10,12', Moneda::formatear(10.124));
    }

    private function obraConPrecio(float $precio): Obra
    {
        $usuario = User::factory()->create(['nombre' => 'Admin', 'rol' => 'admin', 'activo' => true]);
        $this->actingAs($usuario);

        $obra = Obra::query()->create([
            'numero' => 'M-'.uniqid(), 'nombre' => 'Moneda', 'fechaCreacion' => now()->toDateString(),
            'estadoSeguimiento' => 'ABIERTA',
        ]);

        $elemento = ElementoObra::query()->create([
            'tipoElemento' => 'ABERTURA_SIMPLE', 'idObra' => $obra->id, 'numero' => 1,
        ]);

        AberturaSimple::query()->create([
            'cantidad' => 1, 'ancho' => 1000, 'alto' => 1000, 'idTipologia' => 6, 'idSerie' => 10,
            'terminacion' => 'PINTADO_BLANCO', 'numero' => 1, 'colorHerrajes' => '', 'colorPlasticos' => '',
            'ubicacion' => '', 'tipo' => 'MARCO_FIJO', 'idElementoObra' => $elemento->id, 'idRellenoVano' => 1,
            'precioUnitario' => $precio,
        ]);

        return $obra;
    }

    public function test_el_detalle_de_obra_muestra_dos_decimales(): void
    {
        $obra = $this->obraConPrecio(1234);

        $this->get("/obras/{$obra->id}")
            ->assertOk()
            ->assertSee('1.234,00')
            ->assertSee('U$S 1.234,00');
    }

    public function test_el_panel_muestra_dos_decimales(): void
    {
        $this->obraConPrecio(2500);

        $this->get('/')
            ->assertOk()
            ->assertSee('U$S 2.500,00');
    }

    public function test_el_pdf_muestra_dos_decimales(): void
    {
        $obra = $this->obraConPrecio(777);

        $this->get("/obras/{$obra->id}/presupuesto.pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
