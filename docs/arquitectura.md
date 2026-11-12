# Arquitectura

## Capas

```
Rutas (routes/web.php)
   ↓
Controladores (app/Http/Controllers)
   ↓
Servicios de dominio (app/Services)
   ↓        ↘
Modelos    Exportadores (app/Servicios/Exportacion)
(Eloquent)
   ↓
MySQL / SQLite
```

Los controladores no calculan: delegan en servicios. Los servicios de dominio no conocen HTTP.
Los modelos son finos (relaciones, casts, accesores) y conservan los nombres de tabla del sistema
original.

## Mapa de servicios

| Servicio | Responsabilidad |
|---|---|
| `Precios\CalculadoraPrecioAbertura` | Fórmula de precio sobre líneas ya resueltas |
| `Precios\Coeficientes` | Lectura y caché de coeficientes y tablas de precio |
| `Aberturas\CalcularAberturaSimple` | Orquesta: calculadora de tipología → precio → snapshot |
| `Aberturas\Calculadora*` | Cantidades físicas por tipología y serie |
| `Aberturas\Constantes` | Constantes de corte por tipología-serie y medidas de perfil |
| `Aberturas\LectorParametros` / `MapeadorParametros` | Lectura del histórico EAV del Java |
| `Aberturas\ParametrosFormulario` | Opciones para los formularios de alta |
| `Exportacion\PresupuestoPdf` | Presupuesto en PDF con Dompdf |
| `Exportacion\XlsxWriter` | Escritor XLSX propio sobre ZipArchive |

## Modelo de datos

- **111 tablas** de dominio con nombres en español y columnas en camelCase, heredadas del esquema
  SQLite original para que la importación sea trazable.
- Tablas centrales: `obra` → `elemento_obra` → `abertura_simple` (con `parametros`, `lineas`,
  `precioUnitario`), `abertura_compuesta_2`, `estructura_aluminio`.
- Catálogos: `perfil`, `accesorio`, `vidrio_sin_camara`, `serie`, `tipologia`, `tipologia_serie`.
- Parámetros: `definicion_parametro` + `def_param_tipologia_serie` + las 13 tablas
  `valor_parametro_particular_*`.

## Divergencias con el Java original

| Tema | Java | Este port | Motivo |
|---|---|---|---|
| Cantidades de abertura | Se recalculan al abrir la obra | Se guardan como snapshot | Presupuesto reproducible y auditable |
| Constantes de corte | Hardcodeadas por método | Tabla `constantes_calculo` con cita del origen | Mantenibles y auditables |
| Fallback de márgenes | Iteración de `HashMap` | Orden explícito por `idSerie` | Resultado determinista |
| Cliente | Dos tablas incompatibles | Una tabla `clientes` | Evitar duplicación |
| Usuarios | `Usuario`/`Usuarios` | `users` de Laravel | Auth estándar |
| Fechas | Epoch en ms dentro de `DATE` | Accesores que interpretan ms/segundos/ISO | Compatibilidad |
| Reportes | iText + Apache POI | Dompdf + escritor XLSX propio | Sin binarios en hosting compartido |

## Frontend

Sin build: Blade + un CSS propio (`public/css/alucal.css`) + JavaScript puntual para recargar
opciones de perfiles según la serie. Se eligió así porque el hosting no garantiza Node.

## Testing

- `tests/Feature` cubre navegación, cálculo de cada tipología, recálculo del histórico y exportes.
- `RefreshDatabase` corre las 112 migraciones en SQLite para cada test.
- `php artisan test --compact` es la verificación de cierre; `vendor/bin/pint` el formateo.

## Deuda técnica conocida

- Falta cubrir el resto de las tipologías y series (ver spec 0003).
- Los accesorios opcionales (mosquitero, uniblock, umbral) no se calculan todavía.
- No hay edición/baja de obras ni gestión de catálogos desde la UI.
