# Spec 0004 — Presupuestos y exportes

**Estado:** implementada (PDF, XLSX, CSV)
**Depende de:** 0001, 0002, 0003

## Problema

El Java emite el presupuesto de obra en PDF (iText) y Excel (Apache POI), con textos y condiciones
configurables. En el hosting compartido no hay binarios externos ni Node, así que la generación tiene
que resolverse sólo con PHP.

## Alcance

- Presupuesto de obra en PDF, Excel y CSV.
- Totales por obra y por abertura.
- Textos y condiciones tomados de `parametros_presupuesto`.

## Historias de usuario

- **HU1** — Como vendedor quiero enviar el presupuesto en PDF al cliente.
- **HU2** — Como administración quiero el detalle en Excel para costear y comparar.
- **HU3** — Como sistema quiero excluir del presupuesto los elementos sin precio (compuestos y
  huérfanos) para no mostrar filas vacías.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El PDF incluye encabezado de empresa, datos de obra y cliente, introducción, detalle, total y condiciones. |
| R2 | El PDF numera las páginas y usa el símbolo de moneda configurado (`DOLARES_AMERICANOS` → `U$S`). |
| R3 | El XLSX es un libro válido con encabezado en negrita y números como números. |
| R4 | El CSV incluye BOM para que Excel respete los acentos. |
| R5 | Los tres exportes usan la misma colección de elementos presupuestables. |
| R6 | Ningún exporte requiere extensiones ni binarios fuera de PHP. |

## Criterios de aceptación

- **R1/R2** — `GET /obras/18/presupuesto.pdf` → `200 application/pdf`, encabezado `%PDF-1.7`,
  texto "Página 1 de N" y `TOTAL U$S`.
- **R3** — El archivo abre con OpenPyXL y devuelve los valores numéricos como números.
- **R4** — El CSV empieza con `\xEF\xBB\xBF` y muestra acentos correctos.
- **R5** — Una obra con 3 elementos sin abertura no agrega filas vacías al PDF.
- **R6** — `composer show` sólo lista `dompdf/dompdf` como dependencia nueva.

## Evidencia

- `tests/Feature/CalculoAberturaTest.php` (exportes PDF, XLSX y CSV)
- `app/Servicios/Exportacion/{PresupuestoPdf,XlsxWriter}.php`
- `resources/views/presupuesto/pdf.blade.php`
