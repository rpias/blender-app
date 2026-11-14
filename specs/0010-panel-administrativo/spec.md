# Spec 0010 — Panel administrativo

**Estado:** implementada
**Depende de:** 0001, 0002

## Problema

La aplicación funciona pero su interfaz es una barra superior y una columna de contenido: no hay
panel de inicio, no se ve el estado del negocio en un vistazo y la navegación crece con enlaces
sueltos. La carpintería abre el sistema para responder "¿cuánto tengo presupuestado?" y hoy tiene que
entrar obra por obra.

## Alcance

- Shell administrativo con barra lateral, barra superior y área de contenido.
- Panel de inicio con indicadores reales del negocio.
- Navegación agrupada por uso.

Fuera de alcance: compra e integración de una plantilla comercial (ver nota de licencia en el plan).

## Historias de usuario

- **HU1** — Como dueño quiero abrir el sistema y ver cuántas obras tengo abiertas y cuánto está
  presupuestado, sin entrar obra por obra.
- **HU2** — Como vendedor quiero llegar a Obras, Clientes y Catálogos desde una barra lateral fija.
- **HU3** — Como usuario quiero que en el celular la navegación no tape el contenido.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Existe `/panel` protegido por autenticación y es la pantalla de inicio. |
| R2 | El panel muestra obras abiertas, obras cerradas, aberturas cargadas, clientes y monto presupuestado. |
| R3 | El panel lista las últimas 5 obras con cliente, fecha y total. |
| R4 | El panel muestra las tipologías más usadas con su cantidad. |
| R5 | La navegación se agrupa (Operación / Configuración) y respeta el rol. |
| R6 | En pantallas menores a 1.024 px la barra lateral se oculta y se abre desde el botón de menú. |
| R7 | El diseño no requiere Node ni compilación de assets: se sirve con el CSS del proyecto. |

## Criterios de aceptación

- **R1** — `GET /` redirige a `/panel`; un visitante es enviado a `/login`.
- **R2** — Con 7 obras y 8 aberturas, el panel muestra esos números y el total sumado.
- **R3** — La obra más reciente aparece en la lista.
- **R5** — Un vendedor no ve Catálogos ni Usuarios.
- **R6** — A 800 px de ancho la barra lateral no ocupa lugar y el botón la abre.
- **R7** — `public/css/alucal.css` es el único CSS y no hay `public/build`.

## Evidencia

- `tests/Feature/NavegacionBasicaTest.php` — la raíz exige sesión y el panel muestra los indicadores.
- Capturas: panel a 1.440 px y a 820 px (barra lateral colapsada y KPIs en dos columnas).
- Producción con 7 obras y U$S 1.882 presupuestados.

## Nota de licencia

La imagen de referencia es **Sneat** (PixInvent), plantilla **comercial**. No se copian sus archivos:
se reproduce el lenguaje visual (barra lateral oscura, tarjetas con icono, tipografía y espaciados)
con CSS propio. TailAdmin Laravel es libre pero exige Tailwind y compilación, lo que choca con R7 y
con el despliegue por rsync a hosting compartido.
