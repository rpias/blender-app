# Plan 0011 — Responsive y paginación

## Tablas a tarjetas

Cada `<td>` lleva `data-etiqueta="…"`. El CSS, en `@media (max-width: 820px)`:

- oculta `thead`;
- convierte `tr` en tarjeta con borde y radios;
- muestra `td` como fila etiqueta/valor con `::before { content: attr(data-etiqueta) }`;
- deja la celda de acciones a ancho completo y alineada a la derecha.

Es CSS puro: no cambia el HTML semántico ni requiere JavaScript, y sigue funcionando sin CSS.

## Paginador propio

`resources/views/partials/paginacion.blade.php` recibe un `LengthAwarePaginator` y dibuja
anterior, ventana de páginas y siguiente, con `withQueryString()` ya aplicado en el controlador.

## Paginación nueva

| Lista | Antes | Después |
|---|---|---|
| Usuarios | todas | 25 por página |
| Elementos de obra | todos | 15 por página (`elementos.pagina`) |

El detalle de obra sigue cargando todos los elementos para calcular candidatos, vanos y total;
sólo se pagina la tabla visible.

## Riesgos

- La tabla de catálogos tiene inputs editables dentro de celdas: en modo tarjeta se apilan y el botón
  Guardar queda al final de cada tarjeta.
- La paginación del detalle de obra debe conservar el resto de la pantalla (formulario de alta y vanos).
