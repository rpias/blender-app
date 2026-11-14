@if ($paginador->hasPages())
    @php
        $actual = $paginador->currentPage();
        $ultima = $paginador->lastPage();
        $desde = max(1, $actual - 2);
        $hasta = min($ultima, $actual + 2);
    @endphp

    <nav class="paginador" aria-label="Paginación">
        <p class="paginador-resumen">
            {{ $paginador->firstItem() }}–{{ $paginador->lastItem() }} de {{ $paginador->total() }}
        </p>

        <div class="paginador-controles">
            @if ($paginador->onFirstPage())
                <span class="pagina inactiva" aria-disabled="true">Anterior</span>
            @else
                <a class="pagina" href="{{ $paginador->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @if ($desde > 1)
                <a class="pagina" href="{{ $paginador->url(1) }}">1</a>
                @if ($desde > 2)
                    <span class="pagina puntos">…</span>
                @endif
            @endif

            @for ($pagina = $desde; $pagina <= $hasta; $pagina++)
                @if ($pagina === $actual)
                    <span class="pagina activa" aria-current="page">{{ $pagina }}</span>
                @else
                    <a class="pagina" href="{{ $paginador->url($pagina) }}">{{ $pagina }}</a>
                @endif
            @endfor

            @if ($hasta < $ultima)
                @if ($hasta < $ultima - 1)
                    <span class="pagina puntos">…</span>
                @endif
                <a class="pagina" href="{{ $paginador->url($ultima) }}">{{ $ultima }}</a>
            @endif

            @if ($paginador->hasMorePages())
                <a class="pagina" href="{{ $paginador->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="pagina inactiva" aria-disabled="true">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
