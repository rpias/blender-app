<?php

namespace App\Http\Controllers;

use App\Models\ElementoObra;
use App\Models\Obra;
use App\Models\VanoDivision;
use App\Services\Aberturas\CalcularVano;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Vanos compuestos (spec 0009): agrupa aberturas de una obra y cobra los conectores.
 */
class VanoController extends Controller
{
    public function store(Request $request, Obra $obra, CalcularVano $calcular): RedirectResponse
    {
        $datos = $request->validate([
            'hijos' => ['required', 'array', 'min:1'],
            'hijos.*' => ['integer', 'exists:elemento_obra,id'],
            'codigoConector' => ['required', 'string', 'exists:perfil,Numero'],
            'orientacion' => ['required', 'string', 'in:VERTICAL,HORIZONTAL'],
            'posicionMM' => ['required', 'integer', 'min:100', 'max:12000'],
            'terminacion' => ['nullable', 'string', 'max:60'],
        ]);

        $hijos = ElementoObra::query()->whereIn('id', $datos['hijos'])->get();

        foreach ($hijos as $hijo) {
            if ((int) $hijo->idObra !== (int) $obra->id) {
                return back()->withErrors(['hijos' => 'Todas las aberturas deben ser de esta obra.']);
            }

            if ($hijo->idElementoVano !== null) {
                return back()->withErrors(['hijos' => 'Una de las aberturas ya pertenece a un vano.']);
            }
        }

        $numero = (int) $obra->elementos()->max('numero') + 1;

        $vano = $obra->elementos()->create([
            'tipoElemento' => 'ABERTURA_COMPUESTA',
            'numero' => $numero,
        ]);

        VanoDivision::query()->create([
            'idElementoVano' => $vano->id,
            'orientacion' => $datos['orientacion'],
            'posicionMM' => $datos['posicionMM'],
            'codigoConector' => $datos['codigoConector'],
            'terminacion' => $datos['terminacion'] ?? 'PINTADO_BLANCO',
        ]);

        ElementoObra::query()->whereIn('id', $hijos->pluck('id'))->update(['idElementoVano' => $vano->id]);

        $total = $calcular->detalle($vano->fresh())['total'];

        return redirect()
            ->route('obras.show', $obra)
            ->with('estado', "Vano {$numero} creado: {$hijos->count()} aberturas agrupadas, U\$S {$total}.");
    }

    public function destroy(Obra $obra, ElementoObra $vano): RedirectResponse
    {
        if ($vano->tipoElemento !== 'ABERTURA_COMPUESTA') {
            abort(404);
        }

        // Los hijos se conservan: sólo se deshace la agrupación.
        ElementoObra::query()->where('idElementoVano', $vano->id)->update(['idElementoVano' => null]);
        $vano->vanoDivisiones()->delete();
        $vano->delete();

        return redirect()
            ->route('obras.show', $obra)
            ->with('estado', 'Vano deshecho; las aberturas siguen en la obra.');
    }
}
