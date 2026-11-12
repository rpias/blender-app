<?php

namespace App\Console\Commands;

use App\Models\AberturaSimple;
use App\Services\Aberturas\CalcularAberturaSimple;
use App\Services\Aberturas\MapeadorParametros;
use App\Services\Aberturas\SerieSinConstantes;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recalcula las aberturas importadas desde el Java usando sus parámetros
 * guardados (valor_parametro_particular*).
 *
 * Sólo puede recalcular las combinaciones tipología-serie ya portadas; el
 * resto se informa con el motivo.
 */
#[Signature('alucal:recalcular {--obra= : Recalcular sólo una obra} {--dry : No guarda cambios}')]
#[Description('Recalcula precios de las aberturas importadas desde Alucal')]
class RecalcularAberturas extends Command
{
    public function handle(CalcularAberturaSimple $calculadora, MapeadorParametros $mapeador): int
    {
        $consulta = AberturaSimple::query()->orderBy('id');

        if ($this->option('obra')) {
            $consulta->whereIn('idElementoObra', function ($sub) {
                $sub->select('id')->from('elemento_obra')->where('idObra', (int) $this->option('obra'));
            });
        }

        $aberturas = $consulta->get();

        if ($aberturas->isEmpty()) {
            $this->warn('No hay aberturas para recalcular.');

            return self::SUCCESS;
        }

        $soportadas = DB::table('constantes_calculo')
            ->get(['idTipologia', 'idSerie'])
            ->map(fn ($fila) => $fila->idTipologia.':'.$fila->idSerie)
            ->unique()
            ->all();

        $calculadas = 0;
        $omitidas = [];
        $errores = [];

        foreach ($aberturas as $abertura) {
            $clave = $abertura->idTipologia.':'.$abertura->idSerie;

            if (! in_array($clave, $soportadas, true)) {
                $omitidas[$clave] = ($omitidas[$clave] ?? 0) + 1;

                continue;
            }

            $parametros = $mapeador->para($abertura);

            if ($parametros === []) {
                $errores[] = "Abertura {$abertura->id}: sin parámetros legibles.";
                $omitidas[$clave] = ($omitidas[$clave] ?? 0) + 1;

                continue;
            }

            if ($this->option('dry')) {
                $calculadas++;

                continue;
            }

            try {
                $calculadora->ejecutar($abertura, $parametros);
                $calculadas++;
                $this->line(sprintf(
                    '  abertura %-4s %s/%s %.0f×%.0f → U$S %s',
                    $abertura->id,
                    $abertura->idTipologia,
                    $abertura->idSerie,
                    (float) $abertura->ancho,
                    (float) $abertura->alto,
                    number_format((float) $abertura->precioUnitario, 0, ',', '.'),
                ));
            } catch (SerieSinConstantes $error) {
                $errores[] = "Abertura {$abertura->id}: ".$error->getMessage();
                $omitidas[$clave] = ($omitidas[$clave] ?? 0) + 1;
            }
        }

        $huerfanas = AberturaSimple::query()
            ->whereNotIn('idElementoObra', function ($sub) {
                $sub->select('id')->from('elemento_obra');
            })
            ->count();

        $this->newLine();
        $this->info(($this->option('dry') ? '[dry] ' : '')."Recalculadas: {$calculadas} · Omitidas: ".array_sum($omitidas));

        if ($huerfanas > 0) {
            $this->line("Aberturas sin elemento de obra (datos huérfanos del Java): {$huerfanas}");
        }

        if ($omitidas !== []) {
            $this->line('Pendientes por tipología:serie → cantidad');
            foreach ($omitidas as $clave => $cantidad) {
                $this->line("  {$clave} → {$cantidad}");
            }
        }

        foreach ($errores as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
