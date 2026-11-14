<?php

namespace App\Services;

use App\Models\RegistroSuceso;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Escribe el registro de sucesos (spec 0012).
 *
 * Centraliza la sanitización: nunca se guardan contraseñas, tokens ni el cuerpo completo.
 */
class Registro
{
    private const CLAVES_SECRETAS = [
        'password', 'password_confirmation', 'current_password', '_token', 'remember', 'token',
    ];

    /**
     * @param  array<string, mixed>  $datos
     */
    public function registrar(
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        string $descripcion = '',
        array $datos = [],
        ?User $usuario = null,
    ): RegistroSuceso {
        $usuario ??= auth()->user();

        return RegistroSuceso::query()->create([
            'usuario_id' => $usuario?->id,
            'usuario_nombre' => $usuario?->nombre ?? $usuario?->name,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'descripcion' => mb_substr($descripcion !== '' ? $descripcion : $accion, 0, 255),
            'datos' => $datos === [] ? null : $this->recortar($this->sanitizar($datos)),
            'ip' => $this->ip(request()),
        ]);
    }

    /**
     * Registra la petición HTTP que acaba de ejecutarse.
     */
    public function registrarPeticion(Request $request, int $estado): ?RegistroSuceso
    {
        if ($request->user() === null) {
            return null;
        }

        $ruta = $request->route()?->getName() ?? $request->path();

        // El ingreso y la salida los registran los eventos de autenticación.
        if (in_array($ruta, ['login.attempt', 'logout'], true)) {
            return null;
        }

        return $this->registrar(
            accion: $this->accionDe($request->method()),
            entidad: $this->entidadDe((string) $ruta),
            descripcion: mb_substr($request->method().' '.$request->path(), 0, 255),
            datos: [
                'metodo' => $request->method(),
                'ruta' => (string) $ruta,
                'estado' => $estado,
                'campos' => array_keys($request->except(['_token', '_method'])),
                'payload' => $request->except(['_token', '_method']),
            ],
        );
    }

    private function accionDe(string $metodo): string
    {
        return match (strtoupper($metodo)) {
            'POST' => 'crear',
            'PUT', 'PATCH' => 'editar',
            'DELETE' => 'eliminar',
            default => strtolower($metodo),
        };
    }

    private function entidadDe(string $ruta): ?string
    {
        return match (true) {
            str_starts_with($ruta, 'obras.'), str_starts_with($ruta, 'aberturas.') => 'obra',
            str_starts_with($ruta, 'vanos.') => 'vano',
            str_starts_with($ruta, 'clientes.') => 'cliente',
            str_starts_with($ruta, 'catalogos.') => 'catalogo',
            str_starts_with($ruta, 'usuarios.') => 'usuario',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function sanitizar(array $datos, int $profundidad = 0): array
    {
        $limpio = [];

        foreach ($datos as $clave => $valor) {
            if (in_array(mb_strtolower((string) $clave), self::CLAVES_SECRETAS, true) || str_contains(mb_strtolower((string) $clave), 'password')) {
                continue;
            }

            if (is_array($valor) && $profundidad < 3) {
                $valor = $this->sanitizar($valor, $profundidad + 1);
            }

            $limpio[$clave] = $valor;
        }

        return $limpio;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function recortar(array $datos): array
    {
        $recortado = [];
        $cantidad = 0;

        foreach ($datos as $clave => $valor) {
            if ($cantidad >= 30) {
                break;
            }

            if (is_string($valor)) {
                $valor = mb_substr($valor, 0, 1000);
            }

            $recortado[(string) $clave] = $valor;
            $cantidad++;
        }

        return $recortado;
    }

    private function ip(Request $request): ?string
    {
        $ip = $request->ip();

        return $ip !== null ? mb_substr($ip, 0, 45) : null;
    }
}
