@extends('layouts.app')

@section('titulo', 'Registro de sucesos')

@section('contenido')
    <div class="encabezado">
        <div>
            <h2 class="saludo">Registro de sucesos</h2>
            <p class="ayuda">Qué se hizo en el sistema, quién y cuándo. Sólo lectura.</p>
        </div>
    </div>

    <form method="get" class="tarjeta filtros">
        <div class="grilla">
            <label>
                Usuario
                <input type="text" name="usuario" value="{{ $filtros['usuario'] ?? '' }}" placeholder="Nombre">
            </label>
            <label>
                Acción
                <select name="accion">
                    <option value="">Todas</option>
                    @foreach ($acciones as $accion)
                        <option value="{{ $accion }}" @selected(($filtros['accion'] ?? '') === $accion)>{{ $accion }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Entidad
                <select name="entidad">
                    <option value="">Todas</option>
                    @foreach ($entidades as $entidad)
                        <option value="{{ $entidad }}" @selected(($filtros['entidad'] ?? '') === $entidad)>{{ $entidad }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Desde
                <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}">
            </label>
            <label>
                Hasta
                <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}">
            </label>
            <label>
                Texto
                <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Descripción">
            </label>
        </div>
        <div class="acciones-form">
            <button type="submit" class="btn primario">Filtrar</button>
            <a class="link" href="{{ route('registro.index') }}">Limpiar</a>
        </div>
    </form>

    <table class="tabla">
        <thead>
        <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Entidad</th>
            <th>Descripción</th>
            <th>IP</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($sucesos as $suceso)
            <tr>
                <td data-etiqueta="Fecha">{{ $suceso->created_at?->format('d/m/Y H:i') }}</td>
                <td data-etiqueta="Usuario">{{ $suceso->usuario_nombre ?: '—' }}</td>
                <td data-etiqueta="Acción">
                    <span class="chip chip-accion chip-{{ str_replace('_', '-', $suceso->accion) }}">{{ $suceso->accion }}</span>
                </td>
                <td data-etiqueta="Entidad">
                    {{ $suceso->entidad ?: '—' }}@if ($suceso->entidad_id) #{{ $suceso->entidad_id }}@endif
                </td>
                <td data-etiqueta="Descripción">{{ $suceso->descripcion }}</td>
                <td data-etiqueta="IP">{{ $suceso->ip ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="vacio">No hay sucesos registrados con esos filtros.</td></tr>
        @endforelse
        </tbody>
    </table>

    @include('partials.paginacion', ['paginador' => $sucesos])
@endsection
