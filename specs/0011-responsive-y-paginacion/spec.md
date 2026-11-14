# Spec 0011 — Responsive y paginación

**Estado:** implementada
**Depende de:** 0001, 0006, 0010

## Problema

En el celular (440 px) las tablas se comprimen: la columna "Cliente" se parte en tres líneas y las
acciones quedan fuera de alcance. Además hay listas sin paginar (usuarios, elementos de obra) y el
paginador que genera Laravel trae clases de Tailwind, que este proyecto no usa, así que se ve sin
estilo.

## Alcance

- Tablas de la aplicación que se convierten en tarjetas por debajo de 820 px.
- Paginación consistente y con estilo propio en todas las listas.
- Formularios y filtros que se apilan en pantallas chicas.

Fuera de alcance: el PDF (tiene su propia maqueta) y la tabla del editor de catálogos conserva sus
inputs editables dentro de la tarjeta.

## Historias de usuario

- **HU1** — Como vendedor en el celular quiero leer cada obra como una tarjeta con sus datos y su
  acción, sin hacer zoom ni scroll horizontal.
- **HU2** — Como administración quiero que las listas largas se paginen para no cargar todo.
- **HU3** — Como usuario quiero un paginador claro, con página actual y total.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Por debajo de 820 px toda tabla de la app se muestra como tarjetas con etiqueta por dato. |
| R2 | No hay scroll horizontal en ninguna pantalla a 390 px de ancho. |
| R3 | Usuarios y elementos de obra se paginan. |
| R4 | El paginador es propio (sin Tailwind), con anterior/siguiente, páginas y total. |
| R5 | La paginación conserva los filtros de búsqueda. |
| R6 | Filtros, formularios y acciones se apilan a ancho completo en móvil. |

## Evidencia

- `tests/Feature/PaginacionTest.php` — 5 tests: obras de a 20, clientes de a 25, usuarios de a 25,
  elementos de a 15 y conservación de `buscar` en el paginador.
- Medición con Chrome (CDP, 390 px CSS): `scrollWidth = 390` en panel, obras, detalle de obra,
  catálogos y usuarios.
- Capturas a 390 px de obras, detalle de obra, catálogos de perfiles y usuarios, y a 820 px del detalle.
- Producción: `/obras` a 390 px con `scrollWidth = 390` y 49 celdas etiquetadas; el paginador de
  catálogos responde en la página 2 con Anterior/Siguiente.

## Criterios de aceptación

- **R1** — A 440 px `obras.index` muestra 7 tarjetas con etiquetas "Nombre", "Cliente", "Creada", etc.
- **R2** — `document.body.scrollWidth <= 390` en celular (verificado con captura y CSS).
- **R3** — Con 30 usuarios la pantalla muestra 25 y el paginador cambia a la página 2.
- **R4** — El paginador muestra "Anterior", números y "Siguiente" con los colores del panel.
- **R5** — Buscar y pasar a la página 2 mantiene `?buscar=`.
