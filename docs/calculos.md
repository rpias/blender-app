# Cálculos

Resumen operativo. El detalle con citas al código Java está en
[reverse-engineering/precios.md](reverse-engineering/precios.md) y
[reverse-engineering/aberturas.md](reverse-engineering/aberturas.md).

## Precio de una abertura (margen operativo)

```
kg_barra     = cantidad × (largo_mm / 1000) × peso_kg_m
precioMH     = (kgMH × coefDesperdicio × U$S_kg + accesoriosMH) × margenMH
precioMosq   = (kgMosq × coefDesperdicioMosq × U$S_kg + accesoriosMosq) × margenMosq
precioVidrio = max(m² × costo_m² × margen, mínimo) × piezas          [vidrio simple]
precioDVH    = ((ext + int) × 1,2 + cámara × perímetro) × 1,0 × 1,22  [por pieza]
conectores   = kg × 1,1 × U$S_kg × 2,0
premarco     = metros × precio_metro(código)
tapajuntas   = metros × precio_metro(código)
precio       = round(MH) + round(mosq) + round(vidrio) + round(conectores) + round(premarco+tapajuntas)
```

Sobre el precio unitario se aplica `moneda → IVA 22 % → coeficiente de ajuste`, en ese orden.

El precio de accesorio es `precio / cantPrecio / 100` (el catálogo está en centavos).

## Cantidades por tipología

### Marco fijo

- Marco: 2 horizontales y 2 verticales a medida nominal (45-45).
- Contravidrios: `2(1+divHor)(1+divVer)` horizontales y verticales, con descuento de serie
  (HA 62: 44 mm; Serie 20: 58/34; Rotonda 640: 49).
- Divisores: uno por línea, descontando su ancho y 6 mm de juego.
- Vidrio: `(ancho − divisor − 6·n)/(n+1)` por celda.

### Corrediza Domina (2 hojas)

- Marco 9500 a nominal; hojas 9525 (`alto − 64`) y 9520 ×4 (`ancho/2 − 25`).
- Parante central según enganche: 9530 ×2, 9530 + 9540, o 9540 ×2.
- Suplementos 9580 si el vidrio es ≤ 10 mm.
- Vidrio: `(zócalo − 79) × (altoHoja − 111)`, dos paños.

### Corrediza HA 62 (2 hojas)

- Marco 45°: ADR 2464; 90°: ADR 1767/1769 con umbral `ancho − 48`.
- Hojas ADR 1770 ×4 + parante 2465 / 1771.
- Vidrio: `(zócalo − 123) × (altoHoja − 123)`.
- Rodamiento por peso de hoja (230233 / 231007 / 230574 / 230236).

### Corrediza Serie 20 (2 hojas)

- Marco según lateral (205/235/291), cabezal y zócalo 2210/2211/201.
- Zócalo de hoja: `(ancho − 148 − 2·base)/2 + 63` (automático) o sin el +63 (manual).
- Vidrio: `((ancho − 148 − 2·base)/2 + 14) × (altoHoja − 68 − ajustes)`.

### Corrediza Serie 25 (2 hojas)

- Zócalo: `(ancho − 2·base − 6,4 − 2·pieParante + enganche)/2`, con −72 mm si es punzonado manual.
- Parantes `alto − 48`; suplementos DVH 4590; vidrio `(zócalo + 16) × (parantes − zócalo − cabezal + 18)`.
- Ruedas por tipo y peso (230445 / 230235 / 230240).

## Coeficientes principales (valores de la instalación)

| Clave | Valor |
|---|---|
| `COEF_DESPERDICIO_CONECTORES` | 1,1 |
| `MARGEN_OPERATIVO_CONECTORES` | 2,0 |
| `COEF_DESP_VIDRIO` | 1,2 |
| `MARGEN_DVH_FABRICANTE` / `_CARPINTERIA` | 1,0 / 1,22 |
| `COSTO_POR_METRO_CAMARA_6/9/12/16` | 8 / 10 / 12 / 16 |
| `COLONIAL_RECARGO_MINIMO` / `_POR_M2` | 50 / 35 |
| IVA | 22 % (hardcodeado en el original) |

## Verificación

`tests/Feature/CalculoPrecioTest.php` fija los números de referencia del motor;
`Calculo*Test.php` fija las dimensiones de cada tipología.
