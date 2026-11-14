# Spec 0012 — Registro de sucesos

**Estado:** implementada
**Depende de:** 0001, 0006, 0008

## Problema

Hoy no queda rastro de quién cargó, modificó o borró algo. Si un presupuesto cambia de precio o
desaparece una abertura, no hay forma de saber cuándo ni por quién. Los logs de aplicación
(`storage/logs`) existen pero no son consultables desde el sistema y no identifican al usuario.

## Alcance

- Registro en base de datos de toda acción que modifica datos, más ingresos y salidas.
- Consulta con filtros por usuario, acción, entidad, fecha y texto.
- Acceso sólo para administradores.

Fuera de alcance: registro de lecturas (GET), retención automática y exportación del registro.

## Historias de usuario

- **HU1** — Como administración quiero ver quién eliminó una abertura y cuándo.
- **HU2** — Como administración quiero filtrar el registro por usuario y por rango de fechas.
- **HU3** — Como administración quiero saber si hubo intentos de ingreso fallidos.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Toda petición que modifica datos (POST, PUT, PATCH, DELETE) queda registrada con usuario, acción, entidad, descripción, IP y fecha. |
| R2 | El ingreso exitoso, la salida y el intento fallido quedan registrados. |
| R3 | El registro nunca guarda contraseñas ni tokens. |
| R4 | Existe `/registro`, sólo para administradores, con filtros por usuario, acción, entidad, fechas y texto. |
| R5 | El listado se pagina y respeta los filtros al cambiar de página. |
| R6 | El registro es de sólo lectura: no se edita ni se borra desde la interfaz. |

## Evidencia

- `tests/Feature/RegistroSucesosTest.php` — 7 tests; el de ingreso encontró un bug real: el middleware
  guardaba el payload anidado con la contraseña, y ahora la sanitización es recursiva.
- Capturas a 1.280 px y 390 px de `/registro` (tabla y tarjetas, sin scroll horizontal).
- En desarrollo cada alta, baja, ingreso y salida deja su fila en `registro_sucesos`.

## Criterios de aceptación

- **R1** — Crear una obra por HTTP deja una fila con `accion = crear`, `entidad = obra` y el id creado.
- **R2** — Ingresar deja `accion = ingresar`; una contraseña incorrecta deja `intento_fallido`.
- **R3** — Después de un ingreso, ninguna fila contiene la contraseña usada.
- **R4** — Un vendedor recibe 403 en `/registro`; el admin ve la pantalla con filtros.
- **R5** — Con 40 sucesos la pantalla muestra 30 y el paginador cambia de página conservando el filtro.
