# Tareas 0012 — Registro de sucesos

## Modelo

- [x] Migración `registro_sucesos`
- [x] Modelo `RegistroSuceso` y servicio `Registro`

## Captura

- [x] Middleware `RegistrarSuceso` para mutaciones
- [x] Eventos de ingreso, salida e intento fallido (vía `AppServiceProvider`)
- [x] Sanitización recursiva de secretos y recorte del payload

## Consulta

- [x] `SucesoController@index` con filtros y paginación
- [x] Vista `registro/index.blade.php` y enlace en el menú (sólo admin)

## Verificación

- [x] 7 tests: alta, baja, ingreso, intento fallido, sin contraseñas, permisos, filtros y paginación
- [ ] Prueba manual en producción
