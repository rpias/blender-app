<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Ingresar') · Alucal</title>
    <link rel="stylesheet" href="{{ asset('css/alucal.css') }}?v={{ @filemtime(public_path('css/alucal.css')) }}">
</head>
<body class="invitado">
<main class="invitado-caja">
    <div class="invitado-marca">
        <span class="logo">A</span>
        <span>Alucal<small>presupuestos</small></span>
    </div>

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
