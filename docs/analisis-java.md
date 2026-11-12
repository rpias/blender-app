# Análisis del Java original

## Qué se analizó

`Alucal_v5.5.8/Alucal.jar` — aplicación Java de escritorio, **39 MB**, **12.961 clases**, de las
cuales **1.934 son propias**:

| Paquete | Clases | Rol |
|---|---|---|
| `gui` | 1.372 | Pantallas Swing (frames, paneles, diálogos) |
| `services` | 159 | Cálculo de aberturas, precios, reportes, optimización de corte |
| `entities` | 164 | Modelo de dominio y parámetros |
| `gui2` | 79 | Componentes secundarios |
| `daos` | 56 | Persistencia SQLite |
| `util` | 13 | Utilidades (fuentes, imágenes, login) |

El resto son librerías embebidas: Apache POI, iText, conectores MySQL/SQLite, Joda, dom4j.

## Cómo se decompiló

```bash
# 1. extraer solo los paquetes propios (evita 11.000 clases de librerías)
unzip Alucal.jar 'gui/*' 'gui2/*' 'services/*' 'entities/*' 'daos/*' 'util/*' -d _extracted

# 2. decompilar con CFR 0.152
java -jar cfr.jar --outputdir _decompiled $(find _extracted -name '*.class')
```

Resultado: 738 archivos `.java` con el código propio legible (el resto son clases internas y
lambdas). Los artefactos (`jar`, `_extracted`, `_decompiled`) no se versionan: son reproducibles con
los dos comandos de arriba.

## Base de datos

Seis archivos SQLite (`.s3db`) con separación por función:

| Archivo | Contenido |
|---|---|
| `BDAlucal.s3db` | Obras, elementos, aberturas, parámetros, usuarios |
| `BDDefinicionesAberturas.s3db` | Tipologías, series y definiciones de parámetros |
| `BDAccesorios.s3db` | Perfiles, accesorios y compatibilidades |
| `BDVidrios.s3db` | Vidrios simples, laminados y DVH |
| `BDConfiguracion.s3db` | Coeficientes, precios, textos de presupuesto |
| `BDClientes.s3db` | Clientes y contactos (sin datos en la instalación) |

Se inspeccionaron con la herramienta de REA (`inspect_sqlite_database`) y se volcaron a migraciones
con `tools/gen_schema.py`.

## Hallazgos que condicionaron el port

1. **Las cantidades no se persisten**: al abrir una obra, el Java recalcula las aberturas desde sus
   parámetros (`DaoAberturaSimple.java:223`). En MySQL eso habría exigido recalcular siempre; se
   optó por guardar un snapshot.
2. **Sin tabla de constantes de corte**: cada serie tiene sus descuentos dentro del método
   (`AberturaServiceCorrediza` tiene 15.542 líneas). Se extrajeron a `constantes_calculo`.
3. **Rutas incompletas en el original**: `"Falta implementar"` para 4 hojas, `getFijoDoppia` sin
   vidrio, y NPE cuando el par tipología/serie no matchea.
4. **Datos sucios**: 17 aberturas huérfanas (`idElementoObra = -1` o inexistente) y fechas en epoch
   dentro de columnas `DATE`.

## Informes completos

- [Precios](reverse-engineering/precios.md) — motor `MARGEN_OPERATIVO`, fórmulas, coeficientes,
  casos borde y ambigüedades (31 KB).
- [Aberturas](reverse-engineering/aberturas.md) — parámetros, fórmulas por tipología, persistencia y
  particularidades (34 KB).

Ambos informes se generaron leyendo el código decompilado y citan `archivo:línea` en cada fórmula.

## Reproducir el análisis

```bash
curl -sSLo cfr.jar https://repo1.maven.org/maven2/org/benf/cfr/0.152/cfr-0.152.jar
unzip Alucal.jar 'gui/*' 'services/*' 'entities/*' 'daos/*' 'util/*' 'gui2/*' -d _extracted
java -jar cfr.jar --outputdir _decompiled $(find _extracted -name '*.class')
```
