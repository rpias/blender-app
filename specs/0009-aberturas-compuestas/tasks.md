# Tareas 0009 — Aberturas compuestas

## Modelo

- [ ] Migración `vano_division` + `elemento_obra.idElementoVano`
- [ ] Modelo `VanoDivision` y relación en `ElementoObra`
- [ ] Validación: hijos de la misma obra y sin vano previo

## Cálculo

- [ ] `CalcularVano`: suma de hijos + conectores por kilo
- [ ] Snapshot del vano en `elemento_obra.datos`
- [ ] Recalcular el vano cuando cambia un hijo

## UI

- [ ] Formulario "Agrupar en vano" en el detalle de obra
- [ ] Tabla de obra con vano padre e hijos indentados
- [ ] Acciones: recalcular y deshacer vano

## Reportes

- [ ] PDF con el vano y sus hijos
- [ ] Excel y CSV con la misma agrupación

## Verificación

- [ ] Tests: total del vano, hijo duplicado, borrado del vano conserva hijos
- [ ] Prueba en producción con una obra de ejemplo
