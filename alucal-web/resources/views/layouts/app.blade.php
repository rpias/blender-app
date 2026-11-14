@php
    $usuario = auth()->user();
    $esAdmin = $usuario?->esAdministrador();
    $titulo = trim($__env->yieldContent('titulo'));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo !== '' ? $titulo.' · ' : '' }}Alucal</title>
    <link rel="stylesheet" href="{{ asset('css/alucal.css') }}">
</head>
<body class="app-shell">
<input type="checkbox" id="menu-toggle" class="menu-toggle" hidden>

<aside class="sidebar">
    <div class="sidebar-marca">
        <span class="logo">A</span>
        <span>Alucal<small>presupuestos</small></span>
    </div>

    <nav class="sidebar-nav">
        <p class="grupo">Operación</p>
        <a href="{{ route('panel') }}" class="{{ request()->routeIs('panel') ? 'activo' : '' }}">
            <span class="icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg></span> Panel
        </a>
        <a href="{{ route('obras.index') }}" class="{{ request()->routeIs('obras.*') ? 'activo' : '' }}">
            <span class="icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="7" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="7" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="7" cy="18" r="1.4" fill="currentColor" stroke="none"/></svg></span> Obras
        </a>
        <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'activo' : '' }}">
            <span class="icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/></svg></span> Clientes
        </a>

        @if ($esAdmin)
            <p class="grupo">Configuración</p>
            <a href="{{ route('catalogos.index', 'coeficientes') }}" class="{{ request()->routeIs('catalogos.*') ? 'activo' : '' }}">
                <span class="icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/></svg></span> Catálogos
            </a>
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">
                <span class="icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5.5 6-5.5S15 17 15 20"/><path d="M17 11h4M19 9v4"/></svg></span> Usuarios
            </a>
        @endif
    </nav>

    <div class="sidebar-pie">
        <span class="avatar">{{ strtoupper(substr($usuario?->nombre ?? $usuario?->name ?? 'A', 0, 1)) }}</span>
        <div>
            <strong>{{ $usuario?->nombre ?? $usuario?->name }}</strong>
            <small>{{ $esAdmin ? 'Administrador' : 'Vendedor' }}</small>
        </div>
    </div>
</aside>

<div class="cuerpo">
    <header class="barra">
        <label for="menu-toggle" class="menu-boton" aria-label="Menú">☰</label>
        <h1>{{ $titulo !== '' ? $titulo : 'Panel' }}</h1>

        <form method="get" action="{{ route('obras.index') }}" class="buscador-barra">
            <input type="search" name="buscar" placeholder="Buscar obra…" value="{{ request('buscar') }}">
        </form>

        <form method="post" action="{{ route('logout') }}" class="salir">
            @csrf
            <button type="submit" class="btn-chico">Salir</button>
        </form>
    </header>

    <main class="contenido">
        @if (session('estado'))
            <div class="aviso ok">{{ session('estado') }}</div>
        @endif

        @if ($errors->any())
            <div class="aviso error">
                <strong>Revisá los datos:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('contenido')
    </main>
</div>
</body>
</html>
