@php
    /** @var \App\Models\Obra $obra */
    $textos = $textos ?? [];
    $empresa = $empresa ?? null;
    $total = 0.0;
    $moneda = match ($textos['MONEDA'] ?? '') {
        'DOLARES_AMERICANOS' => 'U$S',
        'PESOS' => '$',
        default => $textos['MONEDA'] ?? 'U$S',
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Presupuesto {{ $obra->numero }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; margin: 0; }
        .encabezado { border-bottom: 2px solid #1f6feb; padding-bottom: 8px; margin-bottom: 14px; }
        .encabezado h1 { font-size: 17px; margin: 0; color: #101827; }
        .encabezado .empresa { color: #555; font-size: 9px; margin-top: 3px; }
        .meta { width: 100%; margin-bottom: 14px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta .etiqueta { color: #666; width: 90px; }
        h2 { font-size: 12px; margin: 16px 0 6px; color: #101827; }
        table.detalle { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.detalle th { background: #eef2f8; border: 1px solid #ccd4e0; padding: 5px; text-align: left; font-size: 8.5px; }
        table.detalle td { border: 1px solid #dde1e8; padding: 5px; }
        table.detalle td.num, table.detalle th.num { text-align: right; }
        table.detalle tfoot td { font-weight: bold; background: #f7f9fc; }
        .intro { margin: 8px 0 0; line-height: 1.45; text-align: justify; }
        .condiciones { margin-top: 14px; }
        .condiciones li { margin-bottom: 3px; }
        .nota { margin-top: 12px; font-size: 9px; color: #444; }
        .pie { position: fixed; bottom: -18px; left: 0; right: 0; font-size: 8px; color: #777; text-align: center; }
        ul { padding-left: 16px; margin: 4px 0; }
    </style>
</head>
<body>
<div class="encabezado">
    <h1>Presupuesto de obra Nº {{ $obra->numero }} · {{ $obra->nombre }}</h1>
    <div class="empresa">
        {{ $empresa->nombre ?? $textos['EMPRESA'] ?? 'Blende' }}
        @if (! empty($empresa?->direccion)) · {{ $empresa->direccion }} @endif
        @if (! empty($empresa?->telefono)) · Tel. {{ $empresa->telefono }} @endif
    </div>
</div>

<table class="meta">
    <tr>
        <td class="etiqueta">Cliente</td>
        <td>{{ $obra->cliente ?: '—' }}</td>
        <td class="etiqueta">Fecha</td>
        <td>{{ $obra->fechaCreacion?->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="etiqueta">Ubicación</td>
        <td>{{ $obra->ubicacion ?: '—' }}</td>
        <td class="etiqueta">Arquitecto</td>
        <td>{{ $obra->arquitecto ?: '—' }}</td>
    </tr>
</table>

@if (! empty($textos['TEXTO_INTRODUCCION']))
    <p class="intro">{{ $textos['TEXTO_INTRODUCCION'] }}</p>
@endif

<h2>Detalle de aberturas</h2>
<table class="detalle">
    <thead>
    <tr>
        <th>Nº</th>
        <th>Tipología / Serie</th>
        <th>Medidas (mm)</th>
        <th class="num">Cant.</th>
        <th>Ubicación</th>
        <th>Terminación</th>
        <th class="num">Unitario</th>
        <th class="num">Subtotal</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($elementos as $elemento)
        @php($abertura = $elemento->aberturaSimple)
        @php($subtotal = (float) ($abertura?->precioUnitario ?? 0) * (int) ($abertura?->cantidad ?? 0))
        @php($total += $subtotal)
        <tr>
            <td>{{ $elemento->numero }}</td>
            <td>
                {{ $abertura?->tipologia?->nombre ?? str_replace('_', ' ', (string) $elemento->tipoElemento) }}
                @if ($abertura?->serie) / {{ $abertura->serie->nombre }} @endif
            </td>
            <td>{{ $abertura ? number_format((float) $abertura->ancho, 0, ',', '.').' × '.number_format((float) $abertura->alto, 0, ',', '.') : '—' }}</td>
            <td class="num">{{ $abertura?->cantidad ?? '—' }}</td>
            <td>{{ $abertura?->ubicacion ?: '—' }}</td>
            <td>{{ $abertura?->terminacion ? str_replace('_', ' ', (string) $abertura->terminacion) : '—' }}</td>
            <td class="num">{{ $abertura?->precioUnitario !== null ? number_format((float) $abertura->precioUnitario, 0, ',', '.') : '—' }}</td>
            <td class="num">{{ $abertura?->precioUnitario !== null ? number_format($subtotal, 0, ',', '.') : '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="8">Sin elementos cargados.</td></tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <td colspan="7" class="num">TOTAL {{ $moneda }}</td>
        <td class="num">{{ number_format($total, 0, ',', '.') }}</td>
    </tr>
    </tfoot>
</table>

<div class="condiciones">
    <h2>Condiciones</h2>
    <ul>
        @if (! empty($textos['PLAZO_DE_ENTREGA']))
            <li><strong>Plazo de entrega:</strong> {{ $textos['PLAZO_DE_ENTREGA'] }}</li>
        @endif
        @if (! empty($textos['FORMA_DE_PAGO_OBRA']))
            <li><strong>Forma de pago:</strong> {{ $textos['FORMA_DE_PAGO_OBRA'] }}</li>
        @endif
        @if (! empty($textos['MANT_DE_LA_OFERTA_OBRA']))
            <li><strong>Mantenimiento de la oferta:</strong> {{ $textos['MANT_DE_LA_OFERTA_OBRA'] }}</li>
        @endif
        @if (! empty($textos['IMPUESTOS']))
            <li><strong>Impuestos:</strong> {{ $textos['IMPUESTOS'] }}</li>
        @endif
        @if (! empty($textos['TEXTO_DE_CONDICIONES']))
            <li>{{ $textos['TEXTO_DE_CONDICIONES'] }}</li>
        @endif
    </ul>

    @if (! empty($textos['PIE_DE_PAGINA']))
        <p class="nota">{{ $textos['PIE_DE_PAGINA'] }}</p>
    @endif
</div>

<div class="pie">{{ $textos['CIUDAD'] ?? '' }} · Blende</div>
</body>
</html>
