# Spec 0002 — Motor de precios

**Estado:** implementada
**Depende de:** 0001

## Problema

El precio de una abertura se calcula con una cadena de coeficientes (kilos de aluminio, desperdicio,
margen operativo, accesorios, vidrio, premarco, tapajuntas) que en el Java vive en
`ServiceCalcularPrecio_MargenOperativo` y sus tablas de configuración. Necesitamos el mismo resultado
en PHP, con los coeficientes tomados de la base y no del código.

## Alcance

- Cálculo del precio unitario de una abertura simple.
- Coeficientes, márgenes y precios desde `BDConfiguracion`.
- Vidrio simple y DVH.
- IVA, moneda y coeficiente de ajuste.

Fuera de alcance: motor alternativo `HORAS_TALLER`, estructuras de aluminio, barandas, fachadas
vidriadas, uniblock mix.

## Historias de usuario

- **HU1** — Como carpintería quiero que el precio salga de mis coeficientes actuales, para poder
  ajustar márgenes sin tocar código.
- **HU2** — Como responsable quiero ver el desglose (marco, vidrio, premarco) para explicar el
  número al cliente.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | `precioMH = (kg × coefDesperdicio × U$S/kg + accesorios) × margen` |
| R2 | `kg = cantidad × largo_mm/1000 × peso_kg_m` por barra |
| R3 | El precio de accesorio es `precio / cantPrecio / 100` |
| R4 | Vidrio simple: `max(m² × costo × margen, mínimo) × piezas` |
| R5 | DVH: `((ext + int) × 1,2 + cámara × perímetro) × margenFabricante × margenCarpintería` |
| R6 | Premarco y tapajuntas: metros × precio/metro por código de perfil |
| R7 | Se redondea por bloque (marco, mosquitero, vidrio, conectores, premarco) y luego se suma |
| R8 | El precio final aplica moneda → IVA 22 % → coeficiente de ajuste, en ese orden |
| R9 | Los coeficientes salen de la base; si falta uno, se usa el valor documentado por defecto |

## Criterios de aceptación

- **R1–R3** — Abertura 1000×1000 HA 62: `(5,051 kg × 1,08 × 7,48 + 0) × 1,8 = 73`.
- **R4** — Vidrio de 0,3×0,3 con mínimo 30 y 2 piezas → 60.
- **R5** — Vidrio DVH de 0,382×0,812 en Serie 25 → precio final de la abertura 162.
- **R6** — Premarco 4570 con perímetro 3 m y 11,9 U$S/m → 36.
- **R7** — El total es la suma de los bloques redondeados, no el redondeo del total.
- **R8** — `conMonedaImpuestosYAjuste(100, pesos, 40, IVA, 0,9) = 4392`.
- **R9** — Cambiar `MARGEN_DVH_CARPINTERIA` en la base cambia el precio sin recompilar.

## Evidencia

- `tests/Feature/CalculoPrecioTest.php` (5 tests)
- `app/Services/Precios/CalculadoraPrecioAbertura.php`
- `docs/reverse-engineering/precios.md`
