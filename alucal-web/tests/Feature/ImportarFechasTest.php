<?php

namespace Tests\Feature;

use App\Console\Commands\ImportarAlucal;
use Tests\TestCase;

class ImportarFechasTest extends TestCase
{
    public function test_convierte_epoch_en_milisegundos_a_fecha(): void
    {
        $comando = new ImportarAlucal;
        $metodo = new \ReflectionMethod($comando, 'normalizar');
        $metodo->setAccessible(true);

        // 1712000213715 ms = 2024-04-01 19:36:53 UTC = 16:36:53 en Montevideo
        $this->assertSame('2024-04-01 16:36:53', $metodo->invoke($comando, 1712000213715, 'fechaCreacion'));
    }

    public function test_no_toca_enteros_que_no_son_fechas(): void
    {
        $comando = new ImportarAlucal;
        $metodo = new \ReflectionMethod($comando, 'normalizar');
        $metodo->setAccessible(true);

        $this->assertSame(1234, $metodo->invoke($comando, 1234, 'cantidad'));
        $this->assertSame(1712000213715, $metodo->invoke($comando, 1712000213715, 'idLegacy'));
    }
}
