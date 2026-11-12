@extends('layouts.app')

@section('titulo', 'Nuevo cliente')

@section('contenido')
    <div class="encabezado">
        <h1>Nuevo cliente</h1>
        <a href="{{ route('clientes.index') }}" class="link">Volver</a>
    </div>

    <form method="post" action="{{ route('clientes.store') }}" class="tarjeta">
        @csrf

        <div class="grilla">
            <label>
                Nombre
                <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="160">
            </label>
            <label>
                Empresa
                <input type="text" name="empresa" value="{{ old('empresa') }}" maxlength="160">
            </label>
            <label>
                Teléfono
                <input type="text" name="telefono" value="{{ old('telefono') }}" maxlength="40">
            </label>
            <label>
                Correo
                <input type="email" name="email" value="{{ old('email') }}" maxlength="120">
            </label>
            <label>
                Dirección
                <input type="text" name="direccion" value="{{ old('direccion') }}" maxlength="160">
            </label>
            <label>
                Alias
                <input type="text" name="alias" value="{{ old('alias') }}" maxlength="60">
            </label>
        </div>

        <p class="ayuda">Número asignado: {{ $proximoNumero }}</p>

        <button type="submit" class="btn primario">Guardar cliente</button>
    </form>
@endsection
