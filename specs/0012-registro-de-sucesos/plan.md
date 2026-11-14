# Plan 0012 — Registro de sucesos

## Tabla `registro_sucesos`

| Columna | Tipo | Uso |
|---|---|---|
| id | increments | |
| usuario_id | unsigned int nullable, índice | autor |
| usuario_nombre | string 120 nullable | se guarda copia: el usuario puede borrarse |
| accion | string 40, índice | crear, editar, eliminar, ingresar, salir, intento_fallido |
| entidad | string 60 nullable, índice | obra, abertura, cliente, catalogo, usuario, sesion |
| entidad_id | unsigned int nullable | fila afectada cuando se conoce |
| descripcion | string 255 | texto legible |
| datos | json nullable | método, ruta, estado HTTP y payload sin secretos |
| ip | string 45 nullable | |
| created_at / updated_at | timestamps | |

## Captura

1. **Middleware `RegistrarSuceso`** en el grupo `web`: después de la respuesta, si el método es
   POST/PUT/PATCH/DELETE y hay usuario autenticado, registra método, ruta, estado y payload
   sanitizado. Se excluyen claves `password`, `password_confirmation`, `_token` y `remember`.
2. **Eventos de autenticación** en `AppServiceProvider`: `Login`, `Logout` y `Failed` escriben
   `ingresar`, `salir` y `intento_fallido` (con el correo, nunca la contraseña).

El payload se recorta (máximo 30 claves y 1.000 caracteres por valor) para no inflar la tabla.

## Consulta

`SucesoController@index` con filtros `usuario`, `accion`, `entidad`, `desde`, `hasta` y `buscar`,
paginado de 30 con `withQueryString()`. Vista `registro/index.blade.php` con la tabla en modo
tarjetas en móvil (spec 0011) y el paginador propio.

## Riesgos

- El middleware no conoce el id de la entidad creada; se deja null salvo que la ruta lo exponga.
  La descripción incluye la ruta concreta para no perder contexto.
- Registrar dentro del ciclo de respuesta agrega una escritura por mutación: aceptable para el
  volumen de una carpintería.
