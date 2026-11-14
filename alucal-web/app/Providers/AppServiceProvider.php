<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Registro;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $evento) {
            $usuario = $evento->user instanceof User ? $evento->user : null;

            app(Registro::class)->registrar(
                accion: 'ingresar',
                entidad: 'sesion',
                entidadId: $usuario?->id,
                descripcion: 'Ingreso al sistema',
                datos: ['email' => $usuario?->email],
                usuario: $usuario,
            );
        });

        Event::listen(Logout::class, function (Logout $evento) {
            $usuario = $evento->user instanceof User ? $evento->user : null;

            app(Registro::class)->registrar(
                accion: 'salir',
                entidad: 'sesion',
                entidadId: $usuario?->id,
                descripcion: 'Salida del sistema',
                datos: ['email' => $usuario?->email],
                usuario: $usuario,
            );
        });

        Event::listen(Failed::class, function (Failed $evento) {
            $email = is_string($evento->credentials['email'] ?? null)
                ? (string) $evento->credentials['email']
                : null;

            app(Registro::class)->registrar(
                accion: 'intento_fallido',
                entidad: 'sesion',
                descripcion: 'Intento de ingreso fallido',
                datos: ['email' => $email],
            );
        });
        //
    }
}
