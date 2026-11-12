@php
    use App\Models\Obra;
    $estados = ['ABIERTA' => 'Abierta', 'CERRADA' => 'Cerrada'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Alucal') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/alucal.css') }}">
</head>
<body>
<header class="topbar">
    <a class="marca" href="{{ url('/') }}">ALUCAL<span class="marca-sub">presupuestos</span></a>
    @auth
        <nav>
            <a href="{{ route('obras.index') }}" class="{{ request()->routeIs('obras.*') ? 'activo' : '' }}">Obras</a>
            <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'activo' : '' }}">Clientes</a>
            @if (auth()->user()?->esAdministrador())
                <a href="{{ route('catalogos.index', 'coeficientes') }}" class="{{ request()->routeIs('catalogos.*') ? 'activo' : '' }}">Catálogos</a>
                <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">Usuarios</a>
            @endif
        </nav>
        <form method="post" action="{{ route('logout') }}" class="salir">
            @csrf
            <span>{{ auth()->user()->nombre ?? auth()->user()->name }}</span>
            <button type="submit">Salir</button>
        </form>
    @endauth
</header>

<main class="contenedor">
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
</body>
</html>
