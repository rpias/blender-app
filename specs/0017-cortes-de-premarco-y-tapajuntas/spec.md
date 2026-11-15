# Spec 0017 — Cortes de premarco y tapajuntas

**Estado:** implementada
**Depende de:** 0014, 0015

## Problema

Desde la spec 0015 el premarco y el tapajuntas se **cobran** por metro lineal, pero no aparecían en el
cómputo de materiales: el taller recibía la lista de corte del marco, la hoja y los contravidrios, y
tenía que calcular a mano el premarco (que además lleva escuadras).

## Alcance

- Cortes de premarco y tapajuntas en el cómputo de materiales de la obra.
- Escuadras de premarco contadas por esquina según el perfil.
- El precio **no cambia**: se sigue cobrando por metro lineal (no se duplica el cobro).

Fuera de alcance: clips y escuadras de alineamiento/armado del tapajuntas, y el caso del tapajuntas
que se mide sobre el premarco (se toma la medida de la abertura).

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El cómputo incluye los cortes del premarco elegido, con la suma propia de cada perfil. |
| R2 | Con el lado de abajo son 2 horizontales y 2 verticales; sin él, 1 horizontal y 2 jambas más cortas. |
| R3 | Las escuadras se cuentan por esquina según el perfil (1 o 2) y el perfil 2195 no lleva. |
| R4 | El cómputo incluye los cortes del tapajuntas con el descuento de su código. |
| R5 | El precio de la abertura no cambia por esta funcionalidad. |

## Criterios de aceptación

- **R1** — Premarco 4570 en 1.000 × 1.000 con abajo: dos cortes de **1.032 mm** × 2 y escuadra 230736 ×4.
- **R2** — Sin abajo: 1.032 × 1 y **1.016** × 2, con 2 escuadras.
- **R3** — Premarco 2195 no genera escuadras.
- **R4** — Tapajuntas ADR 62 en 1.000 × 1.000: **1.066 mm** × 2.
- **R5** — El precio de la abertura queda igual que antes.

## Evidencia

- `tests/Feature/CortesPerimetralesTest.php` — 5 tests (tabla de premarco, sin abajo, 2195, tapajuntas
  y cómputo completo con escuadras).
- En producción: abertura con premarco 4570 y tapajuntas ADR 62 → el cómputo de la obra muestra ambos
  perfiles y la escuadra 230736.

## Tablas del original

Los valores salen de `AberturaService.agregarPremarco` (suma al corte, escuadra y escuadras por
esquina) y de la tabla de descuentos de `agregarTapajuntas`. Los códigos sin caso en el original usan
suma 0 / descuento 0.
