<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una sección al rol administrador (spec 0008).
 *
 * Los usuarios sin sesión los maneja el middleware `auth`; acá sólo se decide
 * el rol.
 */
class SoloAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario === null) {
            abort(403, 'Necesitás iniciar sesión.');
        }

        if (! $usuario->esAdministrador()) {
            abort(403, 'Esta sección es sólo para administradores.');
        }

        return $next($request);
    }
}
