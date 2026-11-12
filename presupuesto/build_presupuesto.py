#!/usr/bin/env python3
"""Genera el presupuesto (DOCX) para Aberturas Blende.

Fuente de contenido: INFORME-cortadora-doble-cabezal.md y DIAGNOSTICO-y-PEDIDO.md
"""

from docx import Document
from docx.enum.table import WD_ALIGN_VERTICAL, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING, WD_TAB_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor, Twips

# --------------------------------------------------------------------------
# Paleta y medidas
# --------------------------------------------------------------------------
NAVY = "1B3A5C"
INK = "24303C"
GRAY = "5F6B76"
ACCENT = "0E7C86"
RULE = "D9E0E7"
LIGHT = "F4F6F8"
SAND = "FBF3E7"
AMBER = "B07A32"

CONTENT_CM = 16.6

PPR_ORDER = [
    "w:pStyle", "w:keepNext", "w:keepLines", "w:pageBreakBefore",
    "w:framePr", "w:widowControl", "w:numPr", "w:suppressLineNumbers",
    "w:pBdr", "w:shd", "w:tabs", "w:suppressAutoHyphens", "w:kinsoku",
    "w:wordWrap", "w:overflowPunct", "w:topLinePunct", "w:autoSpaceDE",
    "w:autoSpaceDN", "w:bidi", "w:adjustRightInd", "w:snapToGrid",
    "w:spacing", "w:ind", "w:contextualSpacing", "w:mirrorIndents",
    "w:suppressOverlap", "w:jc", "w:textDirection", "w:textAlignment",
    "w:textboxTightWrap", "w:outlineLvl", "w:divId", "w:cnfStyle", "w:rPr",
    "w:sectPr", "w:pPrChange",
]

TCPR_ORDER = [
    "w:cnfStyle", "w:tcW", "w:gridSpan", "w:hMerge", "w:vMerge",
    "w:tcBorders", "w:shd", "w:noWrap", "w:tcMar", "w:textDirection",
    "w:tcFitText", "w:vAlign", "w:hideMark", "w:headers", "w:cellIns",
    "w:cellDel", "w:cellMerge", "w:tcPrChange",
]

TBLPR_ORDER = [
    "w:tblStyle", "w:tblpPr", "w:tblOverlap", "w:bidiVisual",
    "w:tblStyleRowBandSize", "w:tblStyleColBandSize", "w:tblW", "w:jc",
    "w:tblCellSpacing", "w:tblInd", "w:tblBorders", "w:shd", "w:tblLayout",
    "w:tblCellMar", "w:tblLook", "w:tblCaption", "w:tblDescription",
]


# --------------------------------------------------------------------------
# Utilidades de bajo nivel
# --------------------------------------------------------------------------
def insert_ordered(parent, child, order):
    """Inserta `child` respetando el orden de esquema de OOXML."""
    tags = [qn(tag) for tag in order]
    if child.tag not in tags:
        parent.append(child)
        return
    target = tags.index(child.tag)
    for existing in list(parent):
        if existing.tag in tags and tags.index(existing.tag) > target:
            existing.addprevious(child)
            return
    parent.append(child)


def shade(cell, fill):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), fill)
    insert_ordered(tc_pr, shd, TCPR_ORDER)


def cell_margins(table, top=0.10, bottom=0.10, left=0.18, right=0.18):
    tbl_pr = table._tbl.tblPr
    mar = OxmlElement("w:tblCellMar")
    for edge, value in (("top", top), ("left", left), ("bottom", bottom), ("right", right)):
        el = OxmlElement(f"w:{edge}")
        el.set(qn("w:w"), str(int(value * 567)))
        el.set(qn("w:type"), "dxa")
        mar.append(el)
    insert_ordered(tbl_pr, mar, TBLPR_ORDER)


def table_width(table, cm):
    tbl_pr = table._tbl.tblPr
    for el in tbl_pr.findall(qn("w:tblW")):
        tbl_pr.remove(el)
    width = OxmlElement("w:tblW")
    width.set(qn("w:type"), "dxa")
    width.set(qn("w:w"), str(int(cm * 567)))
    insert_ordered(tbl_pr, width, TBLPR_ORDER)


def fixed_layout(table):
    tbl_pr = table._tbl.tblPr
    layout = OxmlElement("w:tblLayout")
    layout.set(qn("w:type"), "fixed")
    insert_ordered(tbl_pr, layout, TBLPR_ORDER)


def table_borders(table, color=RULE, size=4, inside_color=None):
    tbl_pr = table._tbl.tblPr
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        el = OxmlElement(f"w:{edge}")
        el.set(qn("w:val"), "single")
        el.set(qn("w:sz"), str(size))
        el.set(qn("w:space"), "0")
        if edge.startswith("inside") and inside_color:
            el.set(qn("w:color"), inside_color)
        else:
            el.set(qn("w:color"), color)
        borders.append(el)
    insert_ordered(tbl_pr, borders, TBLPR_ORDER)


def no_borders(table):
    tbl_pr = table._tbl.tblPr
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        el = OxmlElement(f"w:{edge}")
        el.set(qn("w:val"), "nil")
        borders.append(el)
    insert_ordered(tbl_pr, borders, TBLPR_ORDER)


def keep_row(row):
    tr_pr = row._tr.get_or_add_trPr()
    cant = OxmlElement("w:cantSplit")
    tr_pr.append(cant)


def repeat_header(row):
    tr_pr = row._tr.get_or_add_trPr()
    head = OxmlElement("w:tblHeader")
    tr_pr.append(head)


def para_border(paragraph, edge="bottom", color=RULE, size=6, space=4):
    p_pr = paragraph._p.get_or_add_pPr()
    p_bdr = OxmlElement("w:pBdr")
    el = OxmlElement(f"w:{edge}")
    el.set(qn("w:val"), "single")
    el.set(qn("w:sz"), str(size))
    el.set(qn("w:space"), str(space))
    el.set(qn("w:color"), color)
    p_bdr.append(el)
    insert_ordered(p_pr, p_bdr, PPR_ORDER)


def para_shade(paragraph, fill):
    p_pr = paragraph._p.get_or_add_pPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), fill)
    insert_ordered(p_pr, shd, PPR_ORDER)


def add_field(paragraph, instruction, size=8, color=GRAY):
    runs = []
    for kind in ("begin", None, "separate", None, "end"):
        run = paragraph.add_run()
        run.font.size = Pt(size)
        run.font.color.rgb = RGBColor.from_string(color)
        run.font.name = "Calibri"
        if kind == "begin" or kind == "separate" or kind == "end":
            el = OxmlElement("w:fldChar")
            el.set(qn("w:fldCharType"), kind)
            run._r.append(el)
        elif instruction:
            el = OxmlElement("w:instrText")
            el.set(qn("xml:space"), "preserve")
            el.text = instruction
            run._r.append(el)
            instruction = None
        else:
            el = OxmlElement("w:t")
            el.text = "1"
            run._r.append(el)
        runs.append(run)


# --------------------------------------------------------------------------
# Utilidades de contenido
# --------------------------------------------------------------------------
def style_run(run, size=10.5, bold=False, italic=False, color=INK, name="Calibri"):
    run.font.name = name
    run.font.size = Pt(size)
    run.bold = bold
    run.italic = italic
    run.font.color.rgb = RGBColor.from_string(color)
    return run


def body(doc, text, size=10.5, color=INK, space_after=8, space_before=0, italic=False, bold=False):
    p = doc.add_paragraph()
    pf = p.paragraph_format
    pf.space_after = Pt(space_after)
    pf.space_before = Pt(space_before)
    pf.line_spacing = 1.18
    pf.alignment = WD_ALIGN_PARAGRAPH.LEFT
    style_run(p.add_run(text), size=size, color=color, italic=italic, bold=bold)
    return p


def h1(doc, number, title):
    p = doc.add_paragraph()
    pf = p.paragraph_format
    pf.space_before = Pt(22)
    pf.space_after = Pt(9)
    pf.keep_with_next = True
    pf.line_spacing = 1.0
    style_run(p.add_run(f"{number}  "), size=14, bold=True, color=ACCENT)
    style_run(p.add_run(title), size=14, bold=True, color=NAVY)
    para_border(p, "bottom", RULE, 6, 6)
    return p


def h2(doc, label, title):
    p = doc.add_paragraph()
    pf = p.paragraph_format
    pf.space_before = Pt(14)
    pf.space_after = Pt(3)
    pf.keep_with_next = True
    pf.line_spacing = 1.0
    style_run(p.add_run(f"{label}  "), size=8.5, bold=True, color=ACCENT)
    style_run(p.add_run("·  "), size=10, bold=True, color="A6B2BE")
    style_run(p.add_run(title), size=12.5, bold=True, color=NAVY)
    return p


def bullet(doc, text, lead=None, size=10, space_after=4):
    p = doc.add_paragraph(style="List Bullet")
    pf = p.paragraph_format
    pf.space_after = Pt(space_after)
    pf.space_before = Pt(0)
    pf.line_spacing = 1.14
    if lead:
        style_run(p.add_run(lead), size=size, bold=True, color=NAVY)
        style_run(p.add_run(text), size=size, color=INK)
    else:
        if text:
            text = text[0].upper() + text[1:]
        style_run(p.add_run(text), size=size, color=INK)
    return p


def deliverable_line(doc, text):
    p = doc.add_paragraph()
    pf = p.paragraph_format
    pf.space_before = Pt(6)
    pf.space_after = Pt(6)
    pf.left_indent = Cm(0.18)
    pf.right_indent = Cm(0.18)
    pf.line_spacing = 1.1
    style_run(p.add_run("ENTREGABLE   "), size=7.5, bold=True, color=ACCENT)
    style_run(p.add_run(text), size=9.5, italic=True, color=NAVY)
    para_shade(p, LIGHT)
    para_border(p, "left", ACCENT, 12, 6)
    return p


def callout(doc, title, text, fill=SAND, bar=AMBER):
    table = doc.add_table(rows=1, cols=1)
    table_width(table, CONTENT_CM)
    fixed_layout(table)
    no_borders(table)
    cell_margins(table, top=0.24, bottom=0.24, left=0.34, right=0.30)
    cell = table.cell(0, 0)
    cell.width = Cm(CONTENT_CM)
    shade(cell, fill)
    p = cell.paragraphs[0]
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = 1.05
    style_run(p.add_run(title), size=8, bold=True, color=bar)
    p2 = cell.add_paragraph()
    p2.paragraph_format.space_after = Pt(0)
    p2.paragraph_format.line_spacing = 1.16
    style_run(p2.add_run(text), size=9.5, color=INK)
    para_border(p, "left", bar, 18, 8)
    keep_row(table.rows[0])
    return table


def spacer(doc, points=6):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(0)
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.line_spacing = 1.0
    style_run(p.add_run(""), size=points)
    return p


def two_col_table(doc, rows, label_width=3.9, size=9.5):
    table = doc.add_table(rows=len(rows), cols=2)
    table_width(table, CONTENT_CM)
    fixed_layout(table)
    no_borders(table)
    cell_margins(table, top=0.12, bottom=0.12, left=0.0, right=0.2)
    for index, (label, value) in enumerate(rows):
        row = table.rows[index]
        keep_row(row)
        left, right = row.cells
        left.width = Cm(label_width)
        right.width = Cm(CONTENT_CM - label_width)
        left.vertical_alignment = WD_ALIGN_VERTICAL.TOP
        right.vertical_alignment = WD_ALIGN_VERTICAL.TOP
        p = left.paragraphs[0]
        p.paragraph_format.space_after = Pt(0)
        p.paragraph_format.line_spacing = 1.1
        style_run(p.add_run(label), size=size - 0.5, bold=True, color=NAVY)
        p2 = right.paragraphs[0]
        p2.paragraph_format.space_after = Pt(0)
        p2.paragraph_format.line_spacing = 1.14
        style_run(p2.add_run(value), size=size, color=INK)
        para_border(p, "bottom", RULE, 4, 8)
        para_border(p2, "bottom", RULE, 4, 8)
    return table


def grid_table(doc, headers, rows, widths, header_fill=NAVY, size=9):
    table = doc.add_table(rows=len(rows) + 1, cols=len(headers))
    table_width(table, sum(widths))
    fixed_layout(table)
    table_borders(table, RULE, 4)
    cell_margins(table, top=0.10, bottom=0.10, left=0.16, right=0.16)

    header_row = table.rows[0]
    repeat_header(header_row)
    keep_row(header_row)
    for index, text in enumerate(headers):
        cell = header_row.cells[index]
        cell.width = Cm(widths[index])
        shade(cell, header_fill)
        p = cell.paragraphs[0]
        p.paragraph_format.space_after = Pt(0)
        p.paragraph_format.line_spacing = 1.0
        style_run(p.add_run(text.upper()), size=7.5, bold=True, color="FFFFFF")

    for r_index, row_values in enumerate(rows, start=1):
        row = table.rows[r_index]
        keep_row(row)
        for c_index, value in enumerate(row_values):
            cell = row.cells[c_index]
            cell.width = Cm(widths[c_index])
            cell.vertical_alignment = WD_ALIGN_VERTICAL.TOP
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(0)
            p.paragraph_format.line_spacing = 1.12
            bold = c_index == 0
            color = NAVY if bold else INK
            style_run(p.add_run(value), size=size, bold=bold, color=color)
    return table


# --------------------------------------------------------------------------
# Documento
# --------------------------------------------------------------------------
def build(path):
    doc = Document()

    section = doc.sections[0]
    section.page_width = Cm(21)
    section.page_height = Cm(29.7)
    section.left_margin = Cm(2.2)
    section.right_margin = Cm(2.2)
    section.top_margin = Cm(2.0)
    section.bottom_margin = Cm(1.9)
    section.header_distance = Cm(0.9)
    section.footer_distance = Cm(0.9)
    section.different_first_page_header_footer = True

    normal = doc.styles["Normal"]
    normal.font.name = "Calibri"
    normal.font.size = Pt(10.5)
    normal.font.color.rgb = RGBColor.from_string(INK)
    normal.paragraph_format.space_after = Pt(6)
    normal.paragraph_format.line_spacing = 1.15
    rpr = normal.element.get_or_add_rPr()
    lang = OxmlElement("w:lang")
    lang.set(qn("w:val"), "es-UY")
    lang.set(qn("w:eastAsia"), "es-UY")
    rpr.append(lang)

    bullets = doc.styles["List Bullet"]
    bullets.paragraph_format.left_indent = Cm(0.55)
    bullets.paragraph_format.first_line_indent = Cm(-0.35)

    # ---------------- Encabezado y pie (páginas interiores) ----------------
    header = section.header
    hp = header.paragraphs[0]
    hp.paragraph_format.tab_stops.add_tab_stop(Twips(4680), WD_TAB_ALIGNMENT.CLEAR)
    hp.paragraph_format.space_after = Pt(0)
    style_run(hp.add_run("CORTADORA DE DOBLE CABEZAL   ·   ABERTURAS BLENDE"), size=7.5, color=GRAY)
    style_run(hp.add_run("\tPresupuesto · Noviembre 2026"), size=7.5, color=GRAY)
    para_border(hp, "bottom", RULE, 4, 6)

    footer = section.footer
    fp = footer.paragraphs[0]
    fp.paragraph_format.tab_stops.add_tab_stop(Twips(4680), WD_TAB_ALIGNMENT.CLEAR)
    fp.paragraph_format.space_before = Pt(4)
    style_run(fp.add_run("Richard Pias · Consultoría técnica"), size=7.5, color=GRAY)
    style_run(fp.add_run("\tPágina "), size=7.5, color=GRAY)
    add_field(fp, "PAGE")
    style_run(fp.add_run(" de "), size=7.5, color=GRAY)
    add_field(fp, "NUMPAGES")
    para_border(fp, "top", RULE, 4, 6)

    # ---------------- Portada ----------------
    spacer(doc, 26)
    bar = doc.add_table(rows=1, cols=1)
    bar.autofit = False
    fixed_layout(bar)
    no_borders(bar)
    cell_margins(bar, top=0.0, bottom=0.0, left=0.0, right=0.0)
    bar.rows[0].height = Cm(0.16)
    bar.rows[0].height_rule = 2  # exactly
    bar_cell = bar.cell(0, 0)
    bar_cell.width = Cm(2.6)
    shade(bar_cell, ACCENT)
    table_width(bar, 2.6)

    spacer(doc, 10)
    kicker = doc.add_paragraph()
    kicker.paragraph_format.space_after = Pt(2)
    style_run(kicker.add_run("PRESUPUESTO"), size=9, bold=True, color=ACCENT)
    style_run(kicker.add_run("    ·    NOVIEMBRE 2026"), size=9, bold=True, color=GRAY)

    title = doc.add_paragraph()
    title.paragraph_format.space_before = Pt(8)
    title.paragraph_format.space_after = Pt(6)
    title.paragraph_format.line_spacing = 1.0
    style_run(title.add_run("Cortadora de doble cabezal"), size=30, bold=True, color=NAVY)

    title2 = doc.add_paragraph()
    title2.paragraph_format.space_after = Pt(10)
    title2.paragraph_format.line_spacing = 1.0
    style_run(title2.add_run("para perfiles de aluminio"), size=30, bold=True, color=NAVY)

    subtitle = doc.add_paragraph()
    subtitle.paragraph_format.space_after = Pt(18)
    subtitle.paragraph_format.line_spacing = 1.15
    style_run(
        subtitle.add_run(
            "Puesta en valor, respaldo del sistema de control y conexión con la red del taller."
        ),
        size=12.5,
        color=GRAY,
    )
    para_border(subtitle, "bottom", RULE, 6, 14)

    lead = doc.add_paragraph()
    lead.paragraph_format.space_after = Pt(20)
    lead.paragraph_format.line_spacing = 1.3
    style_run(
        lead.add_run(
            "Propuesta de trabajo en cuatro fases: investigación, análisis, planificación e "
            "implementación. El alcance se apoya en el relevamiento técnico ya realizado sobre "
            "esta máquina y en las condiciones reales del taller."
        ),
        size=10.5,
        color=INK,
    )

    meta = doc.add_table(rows=6, cols=2)
    table_width(meta, CONTENT_CM)
    fixed_layout(meta)
    no_borders(meta)
    cell_margins(meta, top=0.16, bottom=0.16, left=0.0, right=0.2)
    meta_rows = [
        ("CLIENTE", "Aberturas Blende"),
        ("PREPARADO POR", "Richard Pias"),
        ("FECHA", "9 de noviembre de 2026"),
        ("VALIDEZ", "15 días corridos"),
        ("MONEDA", "Dólares estadounidenses (USD)"),
        ("FORMA DE PAGO", "100 % contra entrega"),
    ]
    for index, (label, value) in enumerate(meta_rows):
        row = meta.rows[index]
        left, right = row.cells
        left.width = Cm(3.9)
        right.width = Cm(CONTENT_CM - 3.9)
        p = left.paragraphs[0]
        p.paragraph_format.space_after = Pt(0)
        style_run(p.add_run(label), size=7.5, bold=True, color=GRAY)
        p2 = right.paragraphs[0]
        p2.paragraph_format.space_after = Pt(0)
        style_run(p2.add_run(value), size=10, color=INK)

    note = doc.add_paragraph()
    note.paragraph_format.space_before = Pt(282)
    note.paragraph_format.line_spacing = 1.2
    style_run(
        note.add_run(
            "Este documento resume el trabajo detallado en el informe técnico del 7 de noviembre "
            "de 2026 y en el diagnóstico de red del mismo equipo."
        ),
        size=8.5,
        italic=True,
        color=GRAY,
    )

    doc.add_page_break()

    # ---------------- 1. Objeto ----------------
    h1(doc, "1", "Qué propone este presupuesto")

    body(
        doc,
        "La cortadora de doble cabezal concentra hoy dos cosas a la vez: es la máquina que define "
        "el ritmo del despiece y es el equipo con mayor riesgo técnico del taller. Su sistema de "
        "control depende de una computadora industrial que corre un software propietario, en chino, "
        "sin instalador y sin copia de respaldo.",
    )
    body(
        doc,
        "Este presupuesto cubre el trabajo necesario para dejar esa máquina identificada, respaldada, "
        "conectada y documentada. Se organiza en cuatro fases encadenadas y cada una entrega un "
        "resultado concreto que habilita la siguiente.",
    )

    overview = doc.add_table(rows=1, cols=4)
    table_width(overview, CONTENT_CM)
    fixed_layout(overview)
    no_borders(overview)
    table_borders(overview, "FFFFFF", 12, inside_color="FFFFFF")
    cell_margins(overview, top=0.18, bottom=0.18, left=0.20, right=0.20)
    phases = [
        ("FASE 1", "Investigación", "Qué máquina es y cómo está controlada"),
        ("FASE 2", "Análisis", "Qué riesgo corre y qué conviene priorizar"),
        ("FASE 3", "Planificación", "Qué se hace, en qué orden y cómo se verifica"),
        ("FASE 4", "Implementación", "Ejecutar, probar y dejar todo documentado"),
    ]
    for index, (num, name, desc) in enumerate(phases):
        cell = overview.cell(0, index)
        cell.width = Cm(CONTENT_CM / 4)
        shade(cell, LIGHT)
        p = cell.paragraphs[0]
        p.paragraph_format.space_after = Pt(1)
        style_run(p.add_run(num), size=6.8, bold=True, color=ACCENT)
        p2 = cell.add_paragraph()
        p2.paragraph_format.space_after = Pt(1)
        p2.paragraph_format.line_spacing = 1.0
        style_run(p2.add_run(name), size=9.5, bold=True, color=NAVY)
        p3 = cell.add_paragraph()
        p3.paragraph_format.space_after = Pt(0)
        p3.paragraph_format.line_spacing = 1.1
        style_run(p3.add_run(desc), size=8, color=GRAY)
    keep_row(overview.rows[0])

    body(doc, "Al finalizar el trabajo, el taller queda con:", space_before=14, space_after=6)
    bullet(doc, "el disco de la PC clonado y verificado, con dos copias: una en el taller y otra fuera de él.")
    bullet(doc, "la máquina integrada a la red de la oficina y probada con una lista de despiece real, en un segmento aislado y sin salida a internet.")
    bullet(doc, "la ficha técnica completa, con los datos que hoy no están: marca, modelo, año, capacidad de corte y repuestos críticos.")
    bullet(doc, "procedimientos escritos de operación, mantenimiento y fallas, y al personal capacitado para usarlos.")

    # ---------------- 2. Punto de partida ----------------
    h1(doc, "2", "Punto de partida")
    body(
        doc,
        "Todo lo que sigue se verificó sobre esta unidad: las fotos del equipo, la etiqueta del panel "
        "PC, la pantalla del software y el gabinete de control. No es la descripción genérica de la "
        "familia de máquinas.",
    )

    two_col_table(
        doc,
        [
            ("Máquina", "Tronzadora de doble cabezal para perfiles de aluminio. Cortes de 45° y 90°, prensas neumáticas y cabezal móvil de posicionamiento automático."),
            ("Control", "PC industrial de 15 pulgadas (YC-05X7154, Core i5, 8 GB, SSD de 128 GB, S/N 24122803507), enlazada por puerto serie RS-232 sobre COM3 a un controlador de cuatro puertos."),
            ("Software", "Optimizador de despiece propietario, en chino. Sin instalador, sin manual y sin copia de seguridad."),
            ("Identificación", "Marca y modelo sin confirmar: la chapa de características no se lee en el material disponible."),
            ("Instalación", "380 V trifásico. Aire comprimido a 5-8 bar, con un consumo de 60 a 140 L/min."),
            ("Documentación", "No existe manual de esta unidad. Sí hay manuales públicos de la misma clase de máquina."),
        ],
    )

    spacer(doc, 10)
    callout(
        doc,
        "EL RIESGO QUE ORDENA EL PROYECTO",
        "Si el disco de la PC falla, se pierde el software que optimiza y carga las listas de corte. "
        "No hay instalador en internet, no hay copia en el taller y el fabricante puede no responder. "
        "La máquina seguiría moviéndose en modo manual, pero dejaría de producir al ritmo actual. "
        "Por eso el respaldo se hace primero, antes que cualquier otra mejora.",
    )

    # ---------------- 3. Alcance ----------------
    h1(doc, "3", "Alcance del trabajo, fase por fase")
    body(
        doc,
        "Las cuatro fases se encadenan: cada una produce el insumo de la siguiente. La investigación "
        "y el análisis ya están terminados y sus entregables se incluyen en este presupuesto. La "
        "planificación y la implementación se ejecutan a partir de la aprobación.",
    )

    h2(doc, "FASE 1", "Investigación")
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.15
    style_run(p.add_run("Objetivo.  "), size=9.5, bold=True, color=ACCENT)
    style_run(p.add_run("establecer qué máquina es, cómo está gobernada y qué información existe sobre ella."), size=9.5, italic=True, color=GRAY)
    bullet(doc, "relevamiento de la máquina en planta: cabezales, bancada, guías, prensas, pupitre de mando, instalación eléctrica y neumática.")
    bullet(doc, "lectura e interpretación de las etiquetas del panel PC y del gabinete de control.")
    bullet(doc, "identificación de la arquitectura de control: PC industrial, enlace serie y controlador de movimiento.")
    bullet(doc, "ubicación de la máquina dentro de su familia técnica (LJZ2-CNC-500×4200 y equivalentes), con el procedimiento para cerrar marca y modelo en 15 minutos.")
    bullet(doc, "revisión de los consumibles críticos: discos, lubricación, zapatas de prensa y filtros.")
    bullet(doc, "búsqueda y evaluación de manuales públicos de la misma clase de equipo.")
    bullet(doc, "redacción del pedido de documentación al fabricante, en inglés y en chino.")
    deliverable_line(doc, "Informe técnico de 34 páginas, diagnóstico de red y pedido al proveedor.")

    h2(doc, "FASE 2", "Análisis")
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.15
    style_run(p.add_run("Objetivo.  "), size=9.5, bold=True, color=ACCENT)
    style_run(p.add_run("convertir el relevamiento en un mapa de riesgos y en decisiones concretas."), size=9.5, italic=True, color=GRAY)
    bullet(doc, "mapa de riesgos: disco sin respaldo, software irreemplazable, plataforma de PC de 2012, polvo de aluminio y seguridad de operación.")
    bullet(doc, "comparación de las tres vías de conexión posibles (carpeta compartida, sincronización de carpeta y escritorio remoto), con requisitos, ventajas y riesgos de cada una.")
    bullet(doc, "análisis del flujo de trabajo actual, de la lista impresa al corte, y de dónde se pierde tiempo.")
    bullet(doc, "evaluación de la disponibilidad local de repuestos y consumibles.")
    bullet(doc, "priorización de las intervenciones por impacto y por urgencia.")
    deliverable_line(doc, "Matriz de riesgos, recomendación de arquitectura de red y orden de prioridades.")

    h2(doc, "FASE 3", "Planificación")
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.15
    style_run(p.add_run("Objetivo.  "), size=9.5, bold=True, color=ACCENT)
    style_run(p.add_run("definir qué se hace, en qué orden, con qué responsables y cómo se verifica."), size=9.5, italic=True, color=GRAY)
    bullet(doc, "plan de trabajo con hitos, responsables y ventanas de parada de la máquina.")
    bullet(doc, "procedimiento de respaldo del disco: clonado completo, verificación por restauración y criterio de las dos copias.")
    bullet(doc, "diseño de la red: segmento aislado, dirección fija, sin salida a internet y acceso remoto solo por VPN.")
    bullet(doc, "prueba del formato de archivo que acepta el software de despiece y de su lectura desde una ruta de red.")
    bullet(doc, "criterios de aceptación para cada entregable.")
    bullet(doc, "plan de documentación y de capacitación.")
    deliverable_line(doc, "Plan de implementación con procedimientos, cronograma y criterios de aceptación.")

    h2(doc, "FASE 4", "Implementación")
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.15
    style_run(p.add_run("Objetivo.  "), size=9.5, bold=True, color=ACCENT)
    style_run(p.add_run("ejecutar lo planificado, probarlo con producción real y dejar la máquina documentada."), size=9.5, italic=True, color=GRAY)
    bullet(doc, "clonado del disco de la PC industrial y verificación de la copia en un disco de prueba.")
    bullet(doc, "conexión de la máquina a la red del taller por la opción aprobada, con prueba de carga de una lista de despiece real desde la oficina.")
    bullet(doc, "configuración del respaldo automático de la carpeta de trabajos.")
    bullet(doc, "redacción de la documentación operativa: ficha de la máquina, manual abreviado, rutina de mantenimiento y tabla de fallas.")
    bullet(doc, "capacitación del operario y del responsable del taller.")
    bullet(doc, "informe de puesta en marcha con las pruebas realizadas y los resultados obtenidos.")
    deliverable_line(doc, "Copia de seguridad verificada, máquina conectada y probada, documentación operativa y capacitación realizada.")

    # ---------------- 4. Entregables ----------------
    h1(doc, "4", "Entregables")
    body(
        doc,
        "Cada fase cierra con un documento o un resultado verificable. Estos son los ocho entregables "
        "del trabajo completo.",
    )
    grid_table(
        doc,
        ["Entregable", "Formato", "Fase"],
        [
            ("Informe técnico de la máquina", "PDF, 34 páginas", "1"),
            ("Diagnóstico de red y pedido al proveedor", "PDF", "1"),
            ("Matriz de riesgos y recomendación técnica", "PDF", "2"),
            ("Plan de implementación y procedimientos", "PDF", "3"),
            ("Imagen del disco y disco de verificación", "2 discos", "4"),
            ("Documentación operativa del taller", "PDF y copia impresa", "4"),
            ("Capacitación en planta", "2 sesiones", "4"),
            ("Informe de cierre", "PDF", "4"),
        ],
        widths=[11.0, 3.6, 2.0],
    )

    # ---------------- 5. Cronograma ----------------
    h1(doc, "5", "Cronograma")
    body(
        doc,
        "La investigación y el análisis están terminados. El plazo comprometido cubre la "
        "planificación, la implementación y el cierre, y se cuenta desde la aprobación del "
        "presupuesto.",
    )
    grid_table(
        doc,
        ["Etapa", "Duración", "Semana"],
        [
            ("Planificación", "1 semana", "1"),
            ("Implementación: respaldo y red", "2 a 3 semanas", "2 a 4"),
            ("Documentación y capacitación", "1 semana", "5"),
            ("Cierre y aceptación", "3 días", "6"),
        ],
        widths=[9.6, 4.6, 2.4],
    )
    body(
        doc,
        "La máquina no necesita estar detenida más de un turno por intervención. Las fechas se "
        "ajustan a la ventana de parada que el taller asigne.",
        size=9,
        color=GRAY,
        space_before=6,
    )

    # ---------------- 6. Inversión ----------------
    h1(doc, "6", "Inversión")
    body(
        doc,
        "El precio es único por el conjunto del trabajo. No se discrimina por fase porque las cuatro "
        "etapas forman un solo resultado: una máquina respaldada, conectada y documentada.",
    )

    price = doc.add_table(rows=1, cols=2)
    table_width(price, CONTENT_CM)
    fixed_layout(price)
    no_borders(price)
    cell_margins(price, top=0.34, bottom=0.34, left=0.34, right=0.34)
    left, right = price.rows[0].cells
    left.width = Cm(9.4)
    right.width = Cm(7.2)
    shade(left, LIGHT)
    shade(right, LIGHT)
    p = left.paragraphs[0]
    p.paragraph_format.space_after = Pt(2)
    style_run(p.add_run("INVERSIÓN TOTAL"), size=8, bold=True, color=GRAY)
    p2 = left.add_paragraph()
    p2.paragraph_format.space_after = Pt(0)
    p2.paragraph_format.line_spacing = 1.1
    style_run(p2.add_run("Precio cerrado por el alcance descrito en este documento."), size=9, color=INK)

    p3 = right.paragraphs[0]
    p3.paragraph_format.space_after = Pt(0)
    p3.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    style_run(p3.add_run("USD 2.900"), size=26, bold=True, color=NAVY)
    p4 = right.add_paragraph()
    p4.paragraph_format.space_after = Pt(0)
    p4.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    style_run(p4.add_run("IVA incluido"), size=9, color=GRAY)
    keep_row(price.rows[0])

    spacer(doc, 8)
    bullet(doc, "100 % contra entrega y aceptación de los entregables. No se solicita anticipo.", lead="Forma de pago:  ")
    bullet(doc, "15 días corridos desde la fecha de este documento.", lead="Validez de la oferta:  ")
    bullet(doc, "dólares estadounidenses (USD).", lead="Moneda:  ")
    bullet(doc, "el precio incluye el IVA.", lead="Impuestos:  ")
    bullet(doc, "se presupuestan aparte y requieren aprobación escrita antes de ejecutarse.", lead="Trabajos adicionales:  ")

    # ---------------- 7. No incluye ----------------
    h1(doc, "7", "Qué no incluye")
    bullet(doc, "repuestos, discos de corte, lubricantes y consumibles.")
    bullet(doc, "hardware de red (switch, punto de acceso, servidor de archivos o discos externos) y cableado estructurado.")
    bullet(doc, "obra eléctrica y neumática: tablero, puesta a tierra, cañerías y compresor.")
    bullet(doc, "licencias de software de terceros.")
    bullet(doc, "reparación de fallas mecánicas, eléctricas o electrónicas ya existentes.")
    bullet(doc, "ingeniería inversa del protocolo del controlador y desarrollo de software a medida.")
    bullet(doc, "traslados y viáticos fuera de Montevideo.")
    bullet(doc, "producción perdida por la parada de la máquina.")

    # ---------------- 8. Del taller ----------------
    h1(doc, "8", "Qué necesita el proyecto de parte del taller")
    bullet(doc, "acceso a la máquina y al gabinete eléctrico en la ventana acordada.")
    bullet(doc, "contraseña de administrador de Windows y del usuario de la máquina.")
    bullet(doc, "un responsable disponible para las pruebas y la capacitación.")
    bullet(doc, "energía, aire comprimido y red en condiciones de operación.")
    bullet(doc, "decisión sobre la vía de conexión dentro de los cinco días de la reunión de planificación.")

    # ---------------- 9. Supuestos ----------------
    h1(doc, "9", "Supuestos")
    body(
        doc,
        "El alcance y el precio se calculan sobre estas condiciones. Si alguna cambia, se revisa el "
        "plan antes de ejecutar.",
    )
    bullet(doc, "la máquina está operativa y sin fallas mecánicas al momento de la intervención.")
    bullet(doc, "el disco de la PC responde a lectura. Si no responde, se aplica el procedimiento de rescate de datos, que se presupuesta aparte.")
    bullet(doc, "el software de despiece abre archivos desde una ruta local o de red. Si solo admite una ruta local, se implementa la variante de sincronización.")
    bullet(doc, "la red del taller admite un segmento aislado, sin salida a internet.")
    bullet(doc, "los manuales del fabricante, si llegan, se incorporan al trabajo sin costo adicional dentro de los 60 días.")

    # ---------------- 10. Aceptación ----------------
    h1(doc, "10", "Aceptación")
    body(
        doc,
        "La aprobación de este presupuesto habilita el inicio de la planificación. Cualquier cambio "
        "de alcance se acuerda por escrito antes de ejecutarse.",
    )

    body(doc, "Para avanzar, alcanza con tres pasos:", space_before=6, space_after=8)
    for number, text in (
        ("1", "Confirmar la aprobación de este presupuesto, por correo o con la firma de la última página."),
        ("2", "Agendar la reunión de planificación: una hora, en el taller, con el responsable de producción."),
        ("3", "Coordinar la ventana de parada para el clonado del disco, que es la primera intervención."),
    ):
        p = doc.add_paragraph()
        pf = p.paragraph_format
        pf.left_indent = Cm(0.75)
        pf.first_line_indent = Cm(-0.75)
        pf.space_after = Pt(3)
        pf.line_spacing = 1.14
        pf.tab_stops.add_tab_stop(Cm(0.75))
        style_run(p.add_run(f"{number}."), size=10, bold=True, color=ACCENT)
        style_run(p.add_run("\t"), size=10)
        style_run(p.add_run(text), size=10, color=INK)

    decl = doc.add_paragraph()
    decl.paragraph_format.space_before = Pt(14)
    decl.paragraph_format.space_after = Pt(4)
    decl.paragraph_format.line_spacing = 1.2
    style_run(
        decl.add_run(
            "Con la firma de este documento, Aberturas Blende aprueba el alcance, el plazo y la "
            "inversión de USD 2.900 (IVA incluido) descritos en las secciones anteriores."
        ),
        size=9.5,
        italic=True,
        color=GRAY,
    )

    sign = doc.add_table(rows=3, cols=2)
    table_width(sign, CONTENT_CM)
    fixed_layout(sign)
    no_borders(sign)
    cell_margins(sign, top=0.10, bottom=0.10, left=0.0, right=0.6)
    sign.rows[0].height = Cm(1.8)
    left, right = sign.rows[0].cells
    left.width = Cm(8.0)
    right.width = Cm(8.6)
    p = left.paragraphs[0]
    p.paragraph_format.space_before = Pt(34)
    p.paragraph_format.space_after = Pt(2)
    style_run(p.add_run("Por Aberturas Blende"), size=9, bold=True, color=NAVY)
    para_border(p, "top", "9AA7B4", 6, 4)

    p2 = right.paragraphs[0]
    p2.paragraph_format.space_before = Pt(34)
    p2.paragraph_format.space_after = Pt(2)
    style_run(p2.add_run("Por Richard Pias"), size=9, bold=True, color=NAVY)
    para_border(p2, "top", "9AA7B4", 6, 4)

    for index, text in ((1, "Nombre y aclaración"), (2, "Fecha")):
        row = sign.rows[index]
        for cell_index in (0, 1):
            cp = row.cells[cell_index].paragraphs[0]
            cp.paragraph_format.space_after = Pt(4)
            style_run(cp.add_run(text), size=8, color=GRAY)

    spacer(doc, 18)
    body(
        doc,
        "Documento preparado el 9 de noviembre de 2026. Base técnica: informe de la cortadora de "
        "doble cabezal y diagnóstico de red del mismo equipo.",
        size=8,
        color=GRAY,
        italic=True,
    )

    doc.core_properties.title = "Presupuesto · Cortadora de doble cabezal para aluminio"
    doc.core_properties.author = "Richard Pias"
    doc.core_properties.subject = "Presupuesto para Aberturas Blende"
    doc.core_properties.language = "es-UY"
    doc.save(path)
    return path


if __name__ == "__main__":
    print(build("presupuesto/presupuesto-cortadora-aluminio.docx"))
