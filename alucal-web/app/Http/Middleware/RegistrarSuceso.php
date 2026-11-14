<?php

namespace App\Http\Middleware;

use App\Services\Registro;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja registro de toda petición que modifica datos (spec 0012).
 */
class RegistrarSuceso
{
    private const METODOS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if (in_array($request->method(), self::METODOS, true)) {
            app(Registro::class)->registrarPeticion($request, $respuesta->getStatusCode());
        }

        return $respuesta;
    }
}
