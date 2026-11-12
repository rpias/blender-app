<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exporta los datos actuales (SQLite de desarrollo) como INSERTs compatibles
 * con MySQL, para cargarlos en el hosting después de correr las migraciones.
 *
 * No exporta tablas de infraestructura ni la tabla de migraciones.
 */
#[Signature('alucal:exportar-mysql {archivo : Ruta del .sql a generar}')]
#[Description('Genera un dump de datos MySQL para el despliegue')]
class ExportarDatosMysql extends Command
{
    /** @var list<string> */
    private const EXCLUIR = [
        'migrations', 'cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs',
    ];

    public function handle(): int
    {
        $archivo = (string) $this->argument('archivo');
        $tablas = collect(Schema::getTables())->pluck('name')
            ->reject(fn (string $tabla) => in_array($tabla, self::EXCLUIR, true))
            ->values();

        $salida = fopen($archivo, 'w');

        if ($salida === false) {
            $this->error("No se pudo escribir {$archivo}");

            return self::FAILURE;
        }

        fwrite($salida, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $filasTotales = 0;

        foreach ($tablas as $tabla) {
            $filas = DB::table($tabla)->get();

            if ($filas->isEmpty()) {
                continue;
            }

            fwrite($salida, "-- {$tabla}\n");

            foreach ($filas->chunk(200) as $lote) {
                $columnas = array_keys((array) $lote->first());
                $columnasSql = implode(', ', array_map(fn ($columna) => "`{$columna}`", $columnas));

                $valores = $lote->map(function ($fila) {
                    $partes = [];

                    foreach ((array) $fila as $valor) {
                        if ($valor === null) {
                            $partes[] = 'NULL';
                        } elseif (is_bool($valor)) {
                            $partes[] = $valor ? '1' : '0';
                        } elseif (is_int($valor) || is_float($valor)) {
                            $partes[] = (string) $valor;
                        } else {
                            $partes[] = "'".str_replace(
                                ["\\", "'", "\n", "\r", "\x00"],
                                ["\\\\", "\\'", "\\n", "\\r", "\\0"],
                                (string) $valor
                            )."'";
                        }
                    }

                    return '('.implode(', ', $partes).')';
                })->implode(",\n");

                fwrite($salida, "INSERT INTO `{$tabla}` ({$columnasSql}) VALUES\n{$valores};\n");
                $filasTotales += $lote->count();
            }

            fwrite($salida, "\n");
        }

        fwrite($salida, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($salida);

        $this->info('Tablas exportadas: '.$tablas->count()." · filas: {$filasTotales}");
        $this->line('Archivo: '.$archivo);

        return self::SUCCESS;
    }
}
