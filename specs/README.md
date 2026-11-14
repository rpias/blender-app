# Specs

Cada carpeta es una unidad de trabajo con tres archivos:

| Archivo | Contenido |
|---|---|
| `spec.md` | Problema, alcance, historias de usuario, requisitos (`R1`, `R2`, …) y criterios de aceptación verificables |
| `plan.md` | Decisiones técnicas, estructura, fuentes de datos, riesgos |
| `tasks.md` | Tareas con estado (`[ ]` pendiente, `[~]` en curso, `[x]` hecho) |

## Índice

| Spec | Estado | Alcance |
|---|---|---|
| [0001](0001-plataforma-y-datos/spec.md) | implementada | Esquema, importación de datos, login, obras y clientes |
| [0002](0002-motor-de-precios/spec.md) | implementada | Motor de precio por margen operativo, vidrio simple y DVH |
| [0003](0003-tipologias-de-aberturas/spec.md) | parcial | 5 tipologías: marco fijo, Domina, HA 62, S20, S25 |
| [0004](0004-presupuestos-y-exportes/spec.md) | implementada | Presupuesto en PDF, XLSX y CSV |
| [0005](0005-despliegue/spec.md) | implementada | Hosting compartido, MySQL, publicación por SSH |
| [0006](0006-catalogos-editables/spec.md) | implementada | Edición de coeficientes, precios, márgenes, accesorios, perfiles y vidrios |
| [0007](0007-mas-tipologias/spec.md) | parcial | Batiente listo; faltan 2+1, 2+2, paño fijo, proyectante, compuestas |
| [0008](0008-operacion/spec.md) | parcial | Backup y roles listos; falta dominio y alertas |
| [0009](0009-aberturas-compuestas/spec.md) | implementada | Vanos: modelo, cálculo, agrupado y reportes |
| [0010](0010-panel-administrativo/spec.md) | implementada | Shell administrativo y panel de indicadores |
| [0011](0011-responsive-y-paginacion/spec.md) | implementada | Tablas como tarjetas y paginación propia |
| [0012](0012-registro-de-sucesos/spec.md) | implementada | Auditoría de acciones y consulta filtrable |
| [0013](0013-formato-de-moneda/spec.md) | implementada | Importes con dos decimales en toda la app |
| [0014](0014-computo-de-materiales/spec.md) | implementada | Lista de corte y cómputo por obra |
| [0015](0015-premarco-y-tapajuntas/spec.md) | implementada | Cobro de premarco y tapajuntas |

## Cómo agregar una spec

1. Crear `specs/NNNN-nombre-corto/`.
2. Escribir `spec.md` con requisitos numerados y criterios observables **antes** de programar.
3. Escribir `plan.md` con la solución técnica y las dependencias que se agregarán.
4. Listar las tareas en `tasks.md`.
5. Implementar y marcar tareas; dejar la evidencia (test, comando, captura) en `spec.md`.
