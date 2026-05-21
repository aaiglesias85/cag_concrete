## 1. Listado principal

- [x] 1.1 En `initTable` / `getColumnDefs` de `data-tracking.js`, construir el array de acciones del listado como en `invoices.js`: `['detalle']`, más `'edit'` si `permiso.editar`, más `'delete'` si `permiso.eliminar`
- [x] 1.2 Verificar que usuarios solo `ver` no ven iconos de lápiz ni papelera en la columna Actions del listado

## 2. Tablas locales del formulario

- [x] 2.1 Identificar o reutilizar el flag de modo formulario (`new` vs `edit` / solo lectura) usado al abrir crear vs editar vs detalle
- [x] 2.2 Sustituir en tablas de ítems, labor, materials, subcontractors y concrete vendors las llamadas fijas a `getRenderAccionesDataSourceLocal(..., ['edit','delete'])` por `getAccionesDataSourceLocal(formMode, permiso)`
- [x] 2.3 En tabla de adjuntos, combinar `getAccionesDataSourceLocal` con `download` siempre visible en solo lectura; sin `edit`/`delete` sin permiso
- [x] 2.4 Re-inicializar tablas al cambiar de modo (nuevo/edición/detalle) si el array de acciones depende de `formMode`

## 3. Toolbar y plantilla

- [x] 3.1 Auditar `index.html.twig`: confirmar `btn-nuevo-data-tracking`, `btn-eliminar-data-tracking`, `btn-wizard-finalizar` y botones de filas en pestañas bajo `{% if permiso.* %}`
- [x] 3.2 Revisar JS que muestra `btn-eliminar-data-tracking` al seleccionar filas: no debe ejecutarse si el botón no existe en DOM (solo lectura)

## 4. Verificación manual

- [ ] 4.1 Probar usuario solo `ver`: listado con View únicamente; sin New/Delete/Save; tablas internas sin edit/delete; download en adjuntos OK
- [ ] 4.2 Probar usuario `ver`+`editar`: edit en listado y filas locales; sin delete si no hay `eliminar`
- [ ] 4.3 Probar usuario con `agregar`/`eliminar`: acciones completas coherentes en listado y formulario nuevo
