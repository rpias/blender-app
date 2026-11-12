<?php

namespace App\Servicios\Exportacion;

use RuntimeException;
use ZipArchive;

/**
 * Escritor XLSX mínimo sin dependencias externas.
 *
 * Genera un libro de una hoja con cadenas en línea (sin sharedStrings) y dos
 * estilos: normal y encabezado en negrita. Suficiente para el presupuesto de
 * obra en hosting compartido sin sumar PhpSpreadsheet.
 */
class XlsxWriter
{
    /** @var list<list<array{valor: string|float|int|null, encabezado: bool}>> */
    private array $filas = [];

    /** @var array<int, float> ancho de columna en caracteres */
    private array $anchos = [];

    public function __construct(private readonly string $nombreHoja = 'Presupuesto') {}

    /**
     * @param  list<string|float|int|null>  $valores
     */
    public function agregarFila(array $valores, bool $encabezado = false): void
    {
        $fila = [];
        foreach ($valores as $indice => $valor) {
            $fila[] = ['valor' => $valor, 'encabezado' => $encabezado];
            $largo = is_string($valor) ? mb_strlen($valor) : 12;
            $this->anchos[$indice] = max($this->anchos[$indice] ?? 10, min(60, $largo + 2));
        }

        $this->filas[] = $fila;
    }

    public function guardar(string $ruta): void
    {
        $zip = new ZipArchive;

        if ($zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("No se pudo crear el archivo XLSX en {$ruta}.");
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet());

        $zip->close();
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$this->escapar($this->nombreHoja).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            .'</styleSheet>';
    }

    private function sheet(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if ($this->anchos !== []) {
            $xml .= '<cols>';
            foreach ($this->anchos as $indice => $ancho) {
                $xml .= '<col min="'.($indice + 1).'" max="'.($indice + 1).'" width="'.$ancho.'" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';

        foreach ($this->filas as $numeroFila => $fila) {
            $xml .= '<row r="'.($numeroFila + 1).'">';
            foreach ($fila as $numeroColumna => $celda) {
                $xml .= $this->celda($numeroColumna, $numeroFila, $celda['valor'], $celda['encabezado']);
            }
            $xml .= '</row>';
        }

        return $xml.'</sheetData></worksheet>';
    }

    private function celda(int $columna, int $fila, string|float|int|null $valor, bool $encabezado): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $referencia = $this->columna($columna).($fila + 1);
        $estilo = $encabezado ? ' s="1"' : '';

        if (is_int($valor) || is_float($valor)) {
            return '<c r="'.$referencia.'"'.$estilo.'><v>'.$valor.'</v></c>';
        }

        return '<c r="'.$referencia.'"'.$estilo.' t="inlineStr"><is><t xml:space="preserve">'
            .$this->escapar($valor).'</t></is></c>';
    }

    private function columna(int $indice): string
    {
        $letra = '';
        $indice++;

        while ($indice > 0) {
            $resto = ($indice - 1) % 26;
            $letra = chr(65 + $resto).$letra;
            $indice = intdiv($indice - 1, 26);
        }

        return $letra;
    }

    private function escapar(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
