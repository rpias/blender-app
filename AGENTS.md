# Reglas del proyecto (SDD)

Este repositorio usa **Spec-Driven Development**. Antes de escribir código, leé y actualizá la spec
correspondiente en `specs/`.

## Flujo

1. **Spec** (`specs/NNNN-nombre/spec.md`): problema, alcance, historias de usuario, requisitos
   numerados y criterios de aceptación verificables.
2. **Plan** (`plan.md`): decisiones técnicas, modelos, endpoints, dependencias y riesgos.
3. **Tasks** (`tasks.md`): lista ejecutable con estado (`[ ]` pendiente, `[x]` hecho).
4. **Implementación**: código + tests que cubran los criterios de aceptación.
5. **Cierre**: marcar tareas, actualizar `spec.md` si cambió el alcance y dejar evidencia
   (comando de test, captura, salida).

## Reglas

- Todo cambio de comportamiento empieza o actualiza una spec. Si algo no está especificado, se
  escribe primero la spec (aunque sea breve).
- Los requisitos se numeran (`R1`, `R2`, …) y cada uno tiene al menos un criterio de aceptación
  observable.
- Los criterios deben poder verificarse: test automatizado, comando con salida esperada o
  comportamiento visible en la UI.
- No se agregan dependencias sin dejarlo escrito en el `plan.md` de la spec afectada.
- El dominio y los nombres de tablas se mantienen en español, alineados con el sistema original,
  para que la migración de datos siga siendo trazable.
- Toda divergencia deliberada respecto del Java original se documenta en la spec y en
  `docs/arquitectura.md` (sección "Divergencias").

## Convenciones de las specs

```
specs/
  0001-plataforma-y-datos/
    spec.md      qué y para qué
    plan.md      cómo
    tasks.md     tareas y estado
```

- `NNNN`: número secuencial de cuatro dígitos.
- `nombre`: kebab-case, corto.
- Estados de tarea: `[ ]` pendiente, `[~]` en curso, `[x]` hecho.

## Verificación antes de cerrar

```bash
cd alucal-web
php artisan test --compact
vendor/bin/pint app database routes tests --format agent
```
