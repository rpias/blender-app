# Spec 0009 — Aberturas compuestas

**Estado:** parcial (modelo y cálculo)
**Depende de:** 0001, 0002, 0003, 0004

## Problema

Una obra real tiene vanos con varias aberturas unidas por un conector: un ventanal con paño fijo y
corrediza, dos batientes con marco central, etc. Hoy cada abertura se carga suelta, así que el
presupuesto no muestra el vano como una unidad ni cobra los conectores.

En el Java esto existe como `AberturaCompuesta_2` (contenedor) + `DivisionAberturaCompuesta_2`
(conector con perfil, orientación y posición en mm) + elementos hijos. En la base del cliente **no
hay datos usables**: la tabla `abertura_compuesta_2` tiene una fila huérfana, no existe tabla de
divisiones y premarco/tapajuntas están vacíos. Por eso el modelo se define acá, no se importa.

## Alcance

- Contenedor de vano con 2 o más aberturas hijas ya cargadas en la obra.
- Divisiones verticales u horizontales con perfil conector y posición en mm.
- Precio del vano = suma de hijos + conectores (por kilo) + premarco/tapajuntas del contenedor.
- El presupuesto muestra el vano con sus hijos indentados.

Fuera de alcance: mover hijos entre vanos, redimensionar hijos desde el contenedor, relleno de vano.

## Historias de usuario

- **HU1** — Como vendedor quiero agrupar dos aberturas en un vano y que el presupuesto lo muestre
  como una sola línea con su detalle.
- **HU2** — Como administración quiero que el conector se cobre, porque consume perfil.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Un vano pertenece a una obra y agrupa aberturas de esa misma obra. |
| R2 | Cada división guarda orientación (vertical/horizontal), posición en mm y código de perfil conector. |
| R3 | El precio del vano es la suma de los precios de sus hijos más los conectores calculados por kilo. |
| R4 | Un hijo no puede pertenecer a dos vanos. |
| R5 | Al borrar el vano, los hijos se conservan; al borrar un hijo, el vano se recalcula. |
| R6 | El PDF y el Excel muestran el vano con sus hijos. |
| R7 | El vano permite cantidad y se multiplica igual que una abertura simple. |

## Criterios de aceptación

- **R3** — Vano con paño fijo de U$S 100 y corrediza de U$S 200 más un conector de 2 m × 0,9 kg/m:
  el total refleja 300 + conectores.
- **R4** — Intentar agregar una abertura ya agrupada devuelve error de validación.
- **R5** — Borrar el vano deja las aberturas intactas y el total de la obra sin cambios.
- **R6** — El PDF lista el vano en negrita y debajo sus hijos con sus medidas.

## Evidencia

- Migración `2026_01_02_000400_add_vanos_compuestos` aplicada en desarrollo y producción.
- `tests/Feature/CalcularVanoTest.php` — suma de hijos (100 + 200×2) más conector de 2 m ≈ 39,
  y total sin conectores.

## Riesgo principal

El modelo del Java tiene premarco, tapajuntas y relleno de vano propios del contenedor. Se implementa
primero el núcleo (hijos + conectores) y se dejan esos tres como pendientes explícitos.
