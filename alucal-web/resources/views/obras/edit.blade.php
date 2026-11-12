@extends('layouts.app')

@section('titulo', 'Editar obra '.$obra->numero)

@section('contenido')
    <div class="encabezado">
        <div>
            <h1>Editar obra {{ $obra->numero }}</h1>
            <p class="ayuda">Los cambios no recalculan las aberturas: el precio queda como está.</p>
        </div>
        <a class="btn" href="{{ route('obras.show', $obra) }}">Volver</a>
    </div>

    <form method="post" action="{{ route('obras.update', $obra) }}" class="tarjeta">
        @csrf
        @method('patch')

        <div class="grilla">
            <label>
                Número
                <input type="text" name="numero" value="{{ old('numero', $obra->numero) }}" maxlength="20" required>
            </label>
            <label>
                Nombre
                <input type="text" name="nombre" value="{{ old('nombre', $obra->nombre) }}" maxlength="120" required>
            </label>
            <label>
                Cliente
                <select name="idCliente">
                    <option value="">Sin cliente</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected((int) old('idCliente', $obra->idCliente) === (int) $cliente->id)>
                            {{ $cliente->nombre }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                Ubicación
                <input type="text" name="ubicacion" value="{{ old('ubicacion', $obra->ubicacion) }}" maxlength="120">
            </label>
            <label>
                Arquitecto
                <input type="text" name="arquitecto" value="{{ old('arquitecto', $obra->arquitecto) }}" maxlength="120">
            </label>
            <label>
                Fecha
                <input type="date" name="fechaCreacion" value="{{ $obra->fechaCreacion?->format('Y-m-d') }}">
            </label>
            <label>
                Estado
                <select name="estadoSeguimiento">
                    <option value="ABIERTA" @selected(old('estadoSeguimiento', $obra->estadoSeguimiento) === 'ABIERTA')>Abierta</option>
                    <option value="CERRADA" @selected(old('estadoSeguimiento', $obra->estadoSeguimiento) === 'CERRADA')>Cerrada</option>
                </select>
            </label>
            <label>
                Próxima acción
                <input type="date" name="proximaAccionFecha" value="{{ $obra->proximaAccionFecha?->format('Y-m-d') }}">
            </label>
            <label class="ancho-completo">
                Detalle de la próxima acción
                <input type="text" name="proximaAccionDetalle" value="{{ old('proximaAccionDetalle', $obra->proximaAccionDetalle) }}" maxlength="180">
            </label>
        </div>

        <div class="acciones-form">
            <button type="submit" class="btn primario">Guardar cambios</button>
        </div>
    </form>

    <form method="post" action="{{ route('obras.destroy', $obra) }}"
          onsubmit="return confirm('¿Eliminar la obra {{ $obra->numero }} y todas sus aberturas?');"
          class="tarjeta tarjeta-peligro">
        @csrf
        @method('delete')
        <h2>Eliminar obra</h2>
        <p class="ayuda">
            Se borran la obra y sus {{ $obra->elementos()->count() }} elementos, con sus aberturas y precios.
            No se puede deshacer.
        </p>
        <button type="submit" class="btn peligro">Eliminar definitivamente</button>
    </form>
@endsection
