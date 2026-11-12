# Plan 0004 — Presupuestos y exportes

## PDF

- `dompdf/dompdf` (PHP puro, sin binarios): única dependencia agregada para esto.
- Plantilla Blade `resources/views/presupuesto/pdf.blade.php` con estilos embebidos.
- `PresupuestoPdf` arma la vista con `parametros_presupuesto` y `empresa`, renderiza A4 vertical y
  escribe la numeración con `page_text` (`Página {PAGE_NUM} de {PAGE_COUNT}`).
- La ruta responde `inline` para verlo en el navegador.

## Excel

- `App\Servicios\Exportacion\XlsxWriter`: escritor propio sobre `ZipArchive`.
- Genera `[Content_Types].xml`, relaciones, `workbook.xml`, `styles.xml` (normal + negrita) y una
  hoja con textos en línea (`inlineStr`), evitando `sharedStrings`.
- Elegido para no sumar `PhpSpreadsheet` (~20 MB) en un hosting compartido.

## CSV

- `streamDownload` con BOM UTF-8 y separador coma.
- Es el formato de contingencia: si el hosting bloquea `ZipArchive` o `dompdf`, el presupuesto sigue
  saliendo.

## Elementos presupuestables

`Obra::elementosPresupuestables()` devuelve sólo los elementos con `aberturaSimple` calculada. Los
elementos compuestos y los huérfanos del Java quedan afuera hasta que se porten.

## Riesgos

- Dompdf consume memoria con obras muy grandes; el plan del hosting tiene 1536 MB, suficiente para
  decenas de páginas.
- Los nombres de archivo van con el número de obra; no incluyen el cliente para evitar caracteres
  problemáticos.
