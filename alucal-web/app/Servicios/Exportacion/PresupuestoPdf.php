<?php

namespace App\Servicios\Exportacion;

use App\Models\AberturaSimple;
use App\Models\ElementoObra;
use App\Models\Empresa;
use App\Models\Obra;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * Presupuesto de obra en PDF con el formato del original (iText).
 *
 * Usa Dompdf, que sólo necesita PHP (sin binarios externos) y funciona en
 * hosting compartido.
 */
class PresupuestoPdf
{
    public function generar(Obra $obra): string
    {
        $obra->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        return $this->renderizar($obra, $obra->elementosPresupuestables());
    }

    /**
     * Presupuesto de una sola abertura (spec 0004).
     */
    public function generarAbertura(AberturaSimple $abertura): string
    {
        $abertura->load(['tipologia', 'serie']);

        $obra = $abertura->elemento?->obra;
        $obra?->load(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie']);

        $elementos = $abertura->elemento !== null
            ? collect([$abertura->elemento])
            : collect();

        return $this->renderizar($obra, $elementos);
    }

    /**
     * @param  Collection<int, ElementoObra>|iterable<ElementoObra>  $elementos
     */
    private function renderizar(?Obra $obra, iterable $elementos): string
    {
        $html = View::make('presupuesto.pdf', [
            'obra' => $obra,
            'elementos' => $elementos,
            'lineas' => $obra !== null ? (new LineasPresupuesto)->para($obra) : [],
            'empresa' => Empresa::query()->first(),
            'textos' => $this->textos(),
        ])->render();

        $opciones = new Options;
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('chroot', base_path());

        $pdf = new Dompdf($opciones);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $pdf->getCanvas()->page_text(
            480,
            820,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            'DejaVu Sans',
            8,
            [0.45, 0.45, 0.45],
        );

        return (string) $pdf->output();
    }

    /**
     * Textos configurables del presupuesto (tabla ParametrosPresupuesto).
     *
     * @return array<string, string>
     */
    private function textos(): array
    {
        return DB::table('parametros_presupuesto')
            ->pluck('valor', 'clave')
            ->map(fn ($valor) => (string) $valor)
            ->all();
    }
}
