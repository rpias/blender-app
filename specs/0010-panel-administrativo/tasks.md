# Tareas 0010 — Panel administrativo

## Shell

- [x] `layouts/app.blade.php` con barra lateral, barra superior y contenido
- [x] Navegación agrupada con rol aplicado e iconos SVG
- [x] Responsive por CSS sin JS (probado a 820 px y 1.440 px)

## Panel

- [x] `PanelController@index` con indicadores, últimas obras y tipologías
- [x] Vista `panel/index.blade.php` con tarjetas, tabla y barras
- [x] `/` lleva al panel (y exige sesión)

## Verificación

- [x] Tests: acceso a la raíz, indicadores en pantalla
- [ ] Captura del panel en producción
- [ ] Verificar el rechazo del vendedor a Catálogos desde la barra (ya cubierto en CatalogosTest)
