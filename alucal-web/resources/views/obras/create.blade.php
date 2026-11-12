@extends('layouts.app')

@section('titulo', 'Nueva obra')

@section('contenido')
    <div class="encabezado">
        <h1>Nueva obra</h1>
        <a href="{{ route('obras.index') }}" class="link">Volver</a>
    </div>

    <form method="post" action="{{ route('obras.store') }}" class="tarjeta">
        @csrf

        <div class="grilla">
            <label>
                Número
                <input type="text" name="numero" value="{{ old('numero', $proximoNumero) }}" required maxlength="10">
            </label>

            <label>
                Nombre
                <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="255">
            </label>

            <label>
                Cliente
                <input type="text" name="cliente" value="{{ old('cliente') }}" list="clientes" maxlength="160">
                <datalist id="clientes">
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->nombre }}">{{ $cliente->empresa }}</option>
                    @endforeach
                </datalist>
            </label>

            <label>
                Arquitecto
                <input type="text" name="arquitecto" value="{{ old('arquitecto') }}" maxlength="50">
            </label>

            <label>
                Ubicación
                <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" maxlength="50">
            </label>

            <label>
                Estado
                <select name="estadoSeguimiento">
                    <option value="ABIERTA" @selected(old('estadoSeguimiento') === 'ABIERTA')>Abierta</option>
                    <option value="CERRADA" @selected(old('estadoSeguimiento') === 'CERRADA')>Cerrada</option>
                </select>
            </label>
        </div>

        <button type="submit" class="btn primario">Guardar obra</button>
    </form>
@endsection
