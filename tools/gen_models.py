#!/usr/bin/env python3
"""Genera modelos Eloquent para las tablas de dominio de Alucal.

Lee el esquema desde la base SQLite de desarrollo y escribe app/Models/*.php.
Las tablas de infraestructura de Laravel se omiten.
"""
import os
import re
import sqlite3
import sys

SKIP = {
    "cache", "cache_locks", "failed_jobs", "job_batches", "jobs",
    "migrations", "password_reset_tokens", "sessions", "users",
}


def studly(name: str) -> str:
    parts = re.split(r"[_ ]+", name)
    out = []
    for p in parts:
        out.append(p[:1].upper() + p[1:])
    s = "".join(out)
    # nombres que empiezan con dígito o palabra reservada
    return s


def cast_for(decl: str):
    d = (decl or "").upper()
    if "BOOL" in d:
        return "boolean"
    if d.startswith("DATE") or d == "DATE":
        return "date"
    if "DATETIME" in d or "TIMESTAMP" in d:
        return "datetime"
    if any(k in d for k in ("FLOAT", "DOUBLE", "REAL", "NUMERIC", "DECIMAL")):
        return "float"
    if "INT" in d:
        return "integer"
    return None


def main():
    db = sys.argv[1]
    outdir = sys.argv[2]
    con = sqlite3.connect(db)
    cur = con.cursor()
    tables = [r[0] for r in cur.execute(
        "select name from sqlite_master where type='table' and name not like 'sqlite_%' order by name")]
    written = []
    for t in tables:
        if t in SKIP:
            continue
        cols = cur.execute(f'pragma table_info("{t}")').fetchall()
        names = [c[1] for c in cols]
        casts = {}
        for cid, name, decl, notnull, dflt, pk in cols:
            c = cast_for(decl)
            if c and not (pk and "INT" in (decl or "").upper()):
                casts[name] = c
        timestamps = "created_at" in names and "updated_at" in names
        model = studly(t)
        props = "\n".join(f" * @property mixed ${n}" for n in names)
        cast_lines = "".join(f"        '{k}' => '{v}',\n" for k, v in casts.items())
        body = f'''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

/**
 * Tabla: {t}
 *
{props}
 */
class {model} extends Model
{{
    protected $table = '{t}';

    protected $guarded = [];

    public $timestamps = {"true" if timestamps else "false"};
'''
        if cast_lines:
            body += f'''
    protected function casts(): array
    {{
        return [
{cast_lines}        ];
    }}
'''
        body += "}\n"
        with open(os.path.join(outdir, f"{model}.php"), "w") as f:
            f.write(body)
        written.append(model)
    con.close()
    print(f"modelos escritos: {len(written)}")


if __name__ == "__main__":
    main()
