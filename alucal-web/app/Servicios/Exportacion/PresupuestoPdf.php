<?php

namespace App\Servicios\Exportacion;

use App\Models\Empresa;
use App\Models\Obra;
use Dompdf\Dompdf;
use Dompdf\Options;
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

        $html = View::make('presupuesto.pdf', [
            'obra' => $obra,
            'elementos' => $obra->elementosPresupuestables(),
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
