<?php

namespace App\Http\Controllers;

use App\Servicios\Catalogos\RecursoCatalogo;
use App\Servicios\Catalogos\Recursos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Catálogos editables (spec 0006).
 *
 * Un controlador para todos los recursos: el comportamiento se declara en
 * App\Servicios\Catalogos\Recursos.
 */
class CatalogoController extends Controller
{
    public function __construct(private readonly Recursos $recursos) {}

    public function index(Request $request, string $recurso): View
    {
        $config = $this->resolver($recurso);
        $buscar = trim((string) $request->query('buscar', ''));

        $consulta = DB::table($config->tabla);

        if ($buscar !== '' && $config->busqueda !== []) {
            $consulta->where(function ($query) use ($config, $buscar) {
                foreach ($config->busqueda as $columna) {
                    $query->orWhere($columna, 'like', "%{$buscar}%");
                }
            });
        }

        foreach ($config->orden as $columna => $direccion) {
            $consulta->orderBy($columna, $direccion);
        }

        $filas = $consulta->paginate($config->porPagina)->withQueryString();

        return view('catalogos.index', [
            'config' => $config,
            'filas' => $filas,
            'buscar' => $buscar,
            'recurso' => $recurso,
            'todos' => $this->recursos->todos(),
        ]);
    }

    public function update(Request $request, string $recurso): RedirectResponse
    {
        $config = $this->resolver($recurso);

        $request->validate([
            'filas' => ['required', 'array'],
        ]);

        $reglas = [];
        foreach ($config->camposEditables() as $campo) {
            $reglas["filas.*.{$campo}"] = $config->reglas[$campo] ?? 'nullable';
        }

        $request->validate($reglas);

        $filas = (array) $request->input('filas', []);
        $actualizadas = 0;

        DB::transaction(function () use ($config, $filas, &$actualizadas) {
            foreach ($filas as $identificador => $valores) {
                $condiciones = $config->condiciones((string) $identificador);

                if ($condiciones === []) {
                    continue;
                }

                $cambios = [];
                foreach ($config->camposEditables() as $campo) {
                    if (array_key_exists($campo, (array) $valores)) {
                        $cambios[$campo] = $valores[$campo];
                    }
                }

                if ($cambios === []) {
                    continue;
                }

                $actualizadas += DB::table($config->tabla)->where($condiciones)->update($cambios);
            }
        });

        return back()->with('estado', "Catálogo «{$config->titulo}» actualizado: {$actualizadas} filas.");
    }

    private function resolver(string $recurso): RecursoCatalogo
    {
        return $this->recursos->buscar($recurso) ?? abort(404);
    }
}
