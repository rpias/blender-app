# Tareas 0009 — Aberturas compuestas

## Modelo

- [x] Migración `vano_division` + `elemento_obra.idElementoVano`
- [x] Modelo `VanoDivision` y relaciones `hijos` / `vanoDivisiones` en `ElementoObra`
- [x] Validación: hijos de la misma obra y sin vano previo

## Cálculo

- [x] `CalcularVano`: suma de hijos + conectores por kilo (2 tests)
- [ ] Snapshot del vano en `elemento_obra.datos`
- [ ] Recalcular el vano cuando cambia un hijo

## UI

- [x] Formulario "Agrupar aberturas en un vano" con selección, conector, orientación y largo
- [x] Tabla de vanos con las aberturas que agrupa, conector y total
- [x] Deshacer vano conservando las aberturas
- [x] El total de la obra incluye los vanos

## Reportes

- [x] `LineasPresupuesto`: aberturas sueltas y vanos con sus hijos y subtotales
- [x] PDF con el vano en negrita y los hijos indentados
- [x] Excel y CSV con la misma agrupación
- [ ] Snapshot del vano y premarco/tapajuntas del contenedor

## Verificación

- [x] Tests de cálculo: total del vano con y sin conectores
- [x] Tests: vano creado, abertura de otra obra, abertura ya agrupada y deshacer
- [ ] Prueba en producción con una obra de ejemplo
