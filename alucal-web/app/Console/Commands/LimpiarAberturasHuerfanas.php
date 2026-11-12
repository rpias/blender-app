<?php

namespace App\Console\Commands;

use App\Models\AberturaSimple;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Limpia las aberturas huérfanas que dejó el Java: filas cuyo idElementoObra
 * apunta a -1 o a un elemento inexistente.
 *
 * Antes de borrar guarda un respaldo JSON en storage/app.
 */
#[Signature('alucal:limpiar-huerfanas {--force : Borra de verdad (sin esto sólo informa)}')]
#[Description('Archiva y elimina aberturas huérfanas del Java')]
class LimpiarAberturasHuerfanas extends Command
{
    /** @var list<string> */
    private const TABLAS_VALOR = [
        'valor_parametro_particular_perfil',
        'valor_parametro_particular_accesorio',
        'valor_parametro_particular_options',
        'valor_parametro_particular_options_mas_otro',
        'valor_parametro_particular_premarco',
        'valor_parametro_particular_acristalamiento',
        'valor_parametro_particular_divisores',
        'valor_parametro_particular_contravidrios',
        'valor_parametro_particular_mosquitero',
        'valor_parametro_particular_uniblock_mix',
        'valor_parametro_particular_tipo_corrediza',
        'valor_parametro_particular_cierres_corrediza',
        'valor_parametro_particular_integer',
    ];

    public function handle(): int
    {
        $huerfanas = AberturaSimple::query()
            ->whereNotIn('idElementoObra', function ($sub) {
                $sub->select('id')->from('elemento_obra');
            })
            ->get();

        if ($huerfanas->isEmpty()) {
            $this->info('No hay aberturas huérfanas.');

            return self::SUCCESS;
        }

        $this->line("Aberturas huérfanas: {$huerfanas->count()}");

        if (! $this->option('force')) {
            foreach ($huerfanas as $abertura) {
                $this->line(sprintf(
                    '  id %-5s tipología %s/%s  %.0f×%.0f  idElementoObra=%s',
                    $abertura->id,
                    $abertura->idTipologia,
                    $abertura->idSerie,
                    (float) $abertura->ancho,
                    (float) $abertura->alto,
                    $abertura->idElementoObra,
                ));
            }

            $this->warn('Modo informativo. Volvé a ejecutar con --force para archivar y borrar.');

            return self::SUCCESS;
        }

        $respaldo = 'huerfanas-'.now()->format('Ymd-His').'.json';
        $ids = $huerfanas->pluck('id')->all();
        $idsValor = DB::table('valor_parametro_particular')->whereIn('idAberturaSimple', $ids)->pluck('id')->all();

        $contenido = [
            'generado' => now()->toIso8601String(),
            'aberturas' => $huerfanas->map(fn (AberturaSimple $abertura) => $abertura->getAttributes())->all(),
            'valores' => DB::table('valor_parametro_particular')->whereIn('idAberturaSimple', $ids)->get()
                ->map(fn ($fila) => (array) $fila)->all(),
            'valores_tipados' => collect(self::TABLAS_VALOR)
                ->mapWithKeys(fn (string $tabla) => [
                    $tabla => DB::table($tabla)->whereIn('id', $idsValor)->get()
                        ->map(fn ($fila) => (array) $fila)->all(),
                ])
                ->all(),
        ];

        Storage::disk('local')->put($respaldo, json_encode($contenido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        DB::transaction(function () use ($ids, $idsValor) {
            foreach (self::TABLAS_VALOR as $tabla) {
                if ($idsValor !== []) {
                    DB::table($tabla)->whereIn('id', $idsValor)->delete();
                }
            }

            DB::table('valor_parametro_particular')->whereIn('idAberturaSimple', $ids)->delete();
            AberturaSimple::query()->whereIn('id', $ids)->delete();
        });

        $this->info("Archivadas en storage/app/{$respaldo} y eliminadas {$huerfanas->count()} aberturas.");

        return self::SUCCESS;
    }
}
