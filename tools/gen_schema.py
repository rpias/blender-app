#!/usr/bin/env python3
"""Genera migraciones Laravel a partir de los .s3db (SQLite) de Alucal.

Uso:
  python3 gen_schema.py <dir_base_de_datos> <dir_migrations> [--write]

Sin --write solo imprime el plan (dry-run).
"""
import os
import re
import sqlite3
import sys
from collections import OrderedDict

# Orden de precedencia: la primera base que define una tabla gana.
DBS = [
    ("BDAlucal.s3db", "core"),
    ("BDDefinicionesAberturas.s3db", "definiciones"),
    ("BDAccesorios.s3db", "catalogo"),
    ("BDVidrios.s3db", "vidrios"),
    ("BDConfiguracion.s3db", "config"),
    ("BDClientes.s3db", "clientes"),
]

# Tablas que se resuelven a mano (duplicadas o rediseñadas).
MANUAL = {"cliente", "usuario", "usuarios"}


def snake(name: str) -> str:
    s = re.sub(r"(?<=[a-z0-9])([A-Z])", r"_\1", name)
    s = re.sub(r"(?<=[A-Z])([A-Z][a-z])", r"_\1", s)
    return s.lower()


def split_cols(sql: str):
    body = sql[sql.index("(") + 1: sql.rindex(")")]
    parts, depth, cur = [], 0, []
    for ch in body:
        if ch == "(":
            depth += 1
        elif ch == ")":
            depth -= 1
        if ch == "," and depth == 0:
            parts.append("".join(cur))
            cur = []
        else:
            cur.append(ch)
    if cur:
        parts.append("".join(cur))
    return [p.strip() for p in parts if p.strip()]


def parse_type(raw: str):
    t = raw.strip()
    m = re.match(r"^([A-Za-z ]+?)\s*(?:\(\s*(\d+)(?:\s*,\s*(\d+))?\s*\))?$", t)
    if not m:
        return ("string", None, None)
    base, n, scale = m.group(1).strip().upper(), m.group(2), m.group(3)
    return (base, int(n) if n else None, int(scale) if scale else None)


def column_line(col: str, table: str):
    """Devuelve (es_pk_autoincrement, linea_php)."""
    c = col.strip()
    # nombre
    m = re.match(r"""^[\s\["`]?([A-Za-z0-9_ ]+?)[\]"`]?\s+(.*)$""", c, re.S)
    if not m:
        return None
    name, rest = m.group(1).strip(), m.group(2).strip()
    upper = rest.upper()
    is_pk = "PRIMARY KEY" in upper
    autoinc = "AUTOINCREMENT" in upper
    notnull = "NOT NULL" in upper
    unique = "UNIQUE" in upper
    default = re.search(r"DEFAULT\s+(.+?)(?:\s+(?:NOT NULL|NULL|PRIMARY KEY|UNIQUE))?$", rest, re.I | re.S)

    base, n, scale = parse_type(re.sub(r"\s+(NOT NULL|NULL|PRIMARY KEY|AUTOINCREMENT|UNIQUE|DEFAULT\s+\S+.*)$", "", rest, flags=re.I | re.S).strip())

    if is_pk and autoinc:
        return ("id", None)

    if base in ("INTEGER", "INT", "BIGINT", "SMALLINT", "TINYINT"):
        php = f"$table->integer('{name}')" if not notnull else f"$table->integer('{name}')"
    elif base == "BOOLEAN":
        php = f"$table->boolean('{name}')"
    elif base in ("FLOAT", "DOUBLE", "REAL", "NUMERIC"):
        php = f"$table->double('{name}')"
    elif base == "DATE":
        php = f"$table->date('{name}')"
    elif base in ("TIMESTAMP", "DATETIME"):
        php = f"$table->dateTime('{name}')"
    elif base == "BLOB":
        php = f"$table->binary('{name}')"
    elif base in ("TEXT", "CLOB"):
        php = f"$table->text('{name}')"
    elif base == "CHAR":
        php = f"$table->char('{name}', 1)"
    else:
        if n and n > 255:
            php = f"$table->text('{name}')"
        elif n:
            php = f"$table->string('{name}', {n})"
        else:
            php = f"$table->string('{name}')"

    if not is_pk:
        php += "->nullable()" if not notnull else ""

    if default:
        d = default.group(1).strip().strip("'\"")
        if is_pk:
            pass
        elif d.upper() in ("CURRENT_DATE", "CURRENT_TIMESTAMP"):
            pass
        elif d.upper() in ("NULL",):
            pass
        elif re.match(r"^-?\d+(\.\d+)?$", d):
            php += f"->default({d})"
        elif d.upper() in ("TRUE", "FALSE", "1", "0"):
            pass
        else:
            php += "->default('" + d.replace("'", "\\'") + "')"

    if unique:
        php += "->unique()"
    php += ";"
    return (None, php)


def build_migration(table: str, cols, dbname: str):
    lines, has_id = [], False
    for col in cols:
        parsed = column_line(col, table)
        if parsed is None:
            continue
        key, php = parsed
        if key == "id":
            has_id = True
            lines.append("            $table->id();")
        elif php:
            lines.append("            " + php)
    body = "\n".join(lines)
    return f'''<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

/**
 * Origen: {dbname} / tabla original "{table}".
 * Generado por tools/gen_schema.py el {__import__('datetime').date.today()}.
 */
return new class extends Migration
{{
    public function up(): void
    {{
        Schema::create('{snake(table)}', function (Blueprint $table) {{
{body}
        }});
    }}

    public function down(): void
    {{
        Schema::dropIfExists('{snake(table)}');
    }}
}};
'''


def main():
    dbdir, outdir = sys.argv[1], sys.argv[2]
    write = "--write" in sys.argv
    seen = OrderedDict()
    for fname, role in DBS:
        path = os.path.join(dbdir, fname)
        if not os.path.exists(path):
            continue
        con = sqlite3.connect(path)
        cur = con.cursor()
        names = [r[0] for r in cur.execute(
            "select name from sqlite_master where type='table' and name not like 'sqlite_%' order by name")]
        for t in names:
            key = snake(t)
            if key in seen:
                print(f"  ! duplicada '{t}' en {fname} (ya viene de {seen[key][0]})")
                continue
            if key in MANUAL:
                print(f"  - manual: '{t}' ({fname})")
                continue
            sql = cur.execute("select sql from sqlite_master where type='table' and name=?", (t,)).fetchone()[0]
            cols = split_cols(sql)
            seen[key] = (fname, t, cols, role)
        con.close()

    print(f"\ntablas a generar: {len(seen)}")
    os.makedirs(outdir, exist_ok=True)
    if write:
        for old in os.listdir(outdir):
            if old.endswith(".php"):
                os.remove(os.path.join(outdir, old))
    i = 0
    for key, (fname, t, cols, role) in seen.items():
        i += 1
        php = build_migration(t, cols, fname)
        fname_out = f"2026_01_02_{i:06d}_create_{key}_table.php"
        if write:
            with open(os.path.join(outdir, fname_out), "w") as f:
                f.write(php)
        else:
            print(f"\n===== {fname_out} ({fname}:{t}, {len(cols)} cols) =====")
            print(php)


if __name__ == "__main__":
    main()
