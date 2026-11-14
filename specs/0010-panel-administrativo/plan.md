# Plan 0010 — Panel administrativo

## Estructura

```
resources/views/layouts/app.blade.php   shell: sidebar + topbar + contenido
app/Http/Controllers/PanelController.php indicadores y últimas obras
resources/views/panel/index.blade.php    tarjetas, tabla y barras por tipología
public/css/alucal.css                    estilos del shell y de las tarjetas
```

## Layout

- `body.app-shell` con `display: grid; grid-template-columns: 260px 1fr`.
- Barra lateral oscura (`#0f172a`) con marca, grupos y pie con usuario.
- Botón de menú con checkbox oculto (`#menu-toggle`) y `:checked` para abrir en móvil: sin JS.
- Barra superior clara con título de página, buscador de obras y salir.

## Panel

`PanelController@index` calcula:

| Indicador | Consulta |
|---|---|
| Obras abiertas / cerradas | `obra.estadoSeguimiento` |
| Aberturas cargadas | `abertura_simple` con precio |
| Clientes | `clientes` |
| Presupuestado | suma de `precioUnitario × cantidad` + vanos |

Además: últimas 5 obras con su total y top 5 de tipologías por cantidad de aberturas.

## Riesgos

- El total debe coincidir con el detalle de obra: se reutiliza la misma lógica (`LineasPresupuesto`).
- Sin JS no hay `localStorage`: el estado del menú no se recuerda entre páginas.
