@extends('layouts.app')

@section('titulo', 'Ingresar')

@section('contenido')
    <div class="tarjeta estrecha">
        <h1>Ingresar</h1>
        <p class="ayuda">Usá tu cuenta de la carpintería.</p>

        <form method="post" action="{{ route('login.attempt') }}">
            @csrf
            <label>
                Correo
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>
                Contraseña
                <input type="password" name="password" required>
            </label>
            <label class="check">
                <input type="checkbox" name="remember" value="1"> Recordarme
            </label>
            <button type="submit" class="btn primario">Entrar</button>
        </form>
    </div>
@endsection
