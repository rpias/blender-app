# Dominio Alucal

## Jerarquía comercial

```
Carpintería (instalación)
└── Cliente
    └── Obra (número, nombre, ubicación, arquitecto, estado)
        └── ElementoObra (tipo: ABERTURA_SIMPLE, ABERTURA_COMPUESTA, ESTRUCTURA…)
            ├── AberturaSimple  (tipología + serie + medidas + parámetros)
            ├── AberturaCompuesta_2 (paños hijos + conectores)
            ├── EstructuraAluminio
            └── VanoDividido / RellenoVano
```

Cada abertura guarda sus **parámetros particulares** en un modelo EAV: una fila en
`valor_parametro_particular` que apunta a la definición (`definicion_parametro`), más una fila en la
tabla tipada correspondiente (`valor_parametro_particular_perfil`, `_options`, `_acristalamiento`,
…). El valor puede ser un perfil, una opción, un accesorio, un acristalamiento o un entero.

## Catálogos

| Catálogo | Filas | Uso |
|---|---|---|
| `perfil` | 1.728 | Perfiles de aluminio con peso kg/m y códigos alternativos |
| `accesorio` | 1.267 | Accesorios con precio, unidad y `cantPrecio` (precio por N unidades) |
| `accesorio_perfil` | 3.279 | Compatibilidad accesorio ↔ perfil |
| `vidrio_sin_camara` | 53 | Vidrios con precio m², mínimo y margen |
| `serie` | 48 | Líneas comerciales (HA 62, Domina, Serie 20, …) |
| `tipologia` | 20 | Tipos de abertura (corrediza, batiente, marco fijo, …) |
| `tipologia_serie` | 119 | Combinaciones válidas |
| `definicion_parametro` | 165 | Definiciones de parámetros |
| `def_param_tipologia_serie` | 773 | Qué parámetros aplica cada combinación |

## Configuración de precios

| Tabla | Contenido |
|---|---|
| `coeficientes_de_calculo_de_precio` | Coeficientes globales (desperdicio, margen, IVA, DVH, cámaras) |
| `margenes_desperdicios_coef_ajuste` | Margen y desperdicio por tipología × serie (120 filas) |
| `precios_por_kilo` | U$S/kg por terminación y RPT |
| `precio_metro_lineal_premarco` / `_tapajuntas` | Precio por metro según código de perfil |
| `precio_colocacion` | Mano de obra por tipo de colocación |
| `parametros_presupuesto` | Textos y condiciones del presupuesto |

## Terminaciones

`PINTADO_BLANCO`, `PINTADO_NEGRO`, `PINTADO_BRONCE_OSCURO`, `PINTADO_MARRON_AFRICANO`,
`ANODIZADO_NATURAL`, `ELECTROCOLOR_NEGRO`, `ELECTROCOLOR_BRONCE_OSCURO`, entre otras. El precio por
kilo depende de la combinación terminación + RPT.

## Estados de obra

`ABIERTA` y `CERRADA` en `estadoSeguimiento`, más `proximaAccionFecha` y `proximaAccionDetalle` para
el seguimiento comercial.

## Datos históricos

La instalación analizada tenía 7 obras, 8 aberturas visibles y 17 aberturas huérfanas (elementos
borrados sin borrar sus aberturas). Las huérfanas se archivaron en
`storage/app/private/huerfanas-*.json` antes de eliminarlas.
