<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Importa las bases SQLite de Alucal (v5.5.8) a la base de datos de la aplicación.
 *
 * Reglas:
 *  - El nombre de tabla destino es el snake_case del original.
 *  - Las tablas duplicadas entre archivos se toman del origen con prioridad
 *    (core > definiciones > catálogo > vidrios > configuración > clientes).
 *  - Cliente se unifica en "clientes"; Usuario en "users".
 */
#[Signature('alucal:import {directorio : Carpeta con los .s3db}
                            {--solo= : Lista de tablas separadas por coma}
                            {--truncar : Vacía cada tabla destino antes de importar}')]
#[Description('Importa los catálogos y datos de Alucal (SQLite) a la base de la app')]
class ImportarAlucal extends Command
{
    /** Orden de precedencia: la primera base que define una tabla gana. */
    private const ORIGENES = [
        'BDAlucal.s3db' => 'core',
        'BDDefinicionesAberturas.s3db' => 'definiciones',
        'BDAccesorios.s3db' => 'catalogo',
        'BDVidrios.s3db' => 'vidrios',
        'BDConfiguracion.s3db' => 'config',
        'BDClientes.s3db' => 'clientes',
    ];

    /** Tablas que se resuelven a mano. */
    private const ESPECIALES = [
        'cliente' => 'clientes',
        'usuario' => 'users',
        'usuarios' => 'users',
    ];

    /** Renombres de columna por tabla destino. */
    private const COLUMNAS = [
        'clientes' => [
            'telCel' => 'telefono',
            'nroCliente' => 'numero',
            'nombreEmpresa' => 'empresa',
            'telefonoEmpresa' => 'telefono',
            'direccionEmpresa' => 'direccion',
            'aliasEmpresa' => 'alias',
            'fechaHoraAgregado' => 'created_at',
        ],
    ];

    public function handle(): int
    {
        $dir = rtrim((string) $this->argument('directorio'), '/');
        if (! is_dir($dir)) {
            $this->error("No existe el directorio: {$dir}");

            return self::FAILURE;
        }

        $solo = $this->option('solo')
            ? array_map('trim', explode(',', (string) $this->option('solo')))
            : null;

        /** @var array<string, string> $vistas */
        $vistas = [];
        $total = 0;

        foreach (self::ORIGENES as $archivo => $rol) {
            $ruta = "{$dir}/{$archivo}";
            if (! is_file($ruta)) {
                $this->warn("Falta {$archivo}, se omite.");

                continue;
            }

            $pdo = new \PDO('sqlite:'.$ruta);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            $tablas = $pdo->query("select name from sqlite_master where type='table' and name not like 'sqlite_%' order by name")
                ->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tablas as $tabla) {
                $destino = self::ESPECIALES[strtolower((string) $tabla)] ?? $this->snake((string) $tabla);

                if (isset($vistas[$destino])) {
                    continue;
                }

                if ($solo !== null && ! in_array($destino, $solo, true) && ! in_array($tabla, $solo, true)) {
                    continue;
                }

                if (! DB::getSchemaBuilder()->hasTable($destino)) {
                    $this->warn("Sin tabla destino para {$archivo}:{$tabla} → {$destino}");

                    continue;
                }

                $vistas[$destino] = "{$archivo}:{$tabla}";
                $total += $this->importarTabla($pdo, (string) $tabla, $destino);
            }
        }

        $this->newLine();
        $this->info('Tablas importadas: '.count($vistas)." · filas: {$total}");

        return self::SUCCESS;
    }

    private function importarTabla(\PDO $pdo, string $origen, string $destino): int
    {
        $filas = $pdo->query("select * from \"{$origen}\"")->fetchAll();

        if ($filas === []) {
            return 0;
        }

        $columnasDestino = DB::getSchemaBuilder()->getColumnListing($destino);
        $renombres = self::COLUMNAS[$destino] ?? [];

        if ($this->option('truncar')) {
            DB::table($destino)->delete();
        }

        $lotes = [];
        foreach ($filas as $fila) {
            $nueva = [];
            foreach ($fila as $col => $valor) {
                $col = $renombres[$col] ?? $col;
                if (! in_array($col, $columnasDestino, true)) {
                    continue;
                }
                $nueva[$col] = $this->normalizar($valor, $col);
            }

            if ($destino === 'clientes') {
                $nueva['created_at'] = $nueva['created_at'] ?? now();
                $nueva['updated_at'] = $nueva['updated_at'] ?? now();
            }

            $lotes[] = $nueva;
        }

        foreach (array_chunk($lotes, 200) as $chunk) {
            DB::table($destino)->insert($chunk);
        }

        $this->line(sprintf('  %-42s → %-38s %5d', $origen, $destino, count($lotes)));

        return count($lotes);
    }

    /** Columnas de fecha que el original guarda como epoch en milisegundos. */
    private const FECHAS = [
        'fechaCreacion', 'fechaUltModificacion', 'proximaAccionFecha',
        'fecha', 'fechaAlta', 'fechaBaja',
    ];

    private function normalizar(mixed $valor, ?string $columna = null): mixed
    {
        if (is_string($valor) && ! mb_check_encoding($valor, 'UTF-8')) {
            return mb_convert_encoding($valor, 'UTF-8', 'Windows-1252');
        }

        if ($columna !== null && is_numeric($valor) && (int) $valor > 100000000000) {
            if (in_array($columna, self::FECHAS, true) || str_starts_with($columna, 'fecha')) {
                return Carbon::createFromTimestampMsUTC((int) $valor)
                    ->setTimezone('America/Montevideo')
                    ->toDateTimeString();
            }
        }

        return $valor;
    }

    private function snake(string $name): string
    {
        $s = preg_replace('/(?<=[a-z0-9])([A-Z])/', '_$1', $name);
        $s = preg_replace('/(?<=[A-Z])([A-Z][a-z])/', '_$1', (string) $s);

        return strtolower((string) $s);
    }
}
