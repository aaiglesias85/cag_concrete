## Why

En la pantalla admin de Data Tracking, usuarios con permiso solo de **ver** (`ver=1`, sin `editar`, `agregar` ni `eliminar`) siguen viendo botones de **Edit** y **Delete** en el listado principal y en tablas internas del formulario (ítems, labor, materiales, subcontratos, adjuntos, etc.). Eso contradice el modelo de permisos del módulo (`agregar`, `editar`, `eliminar`) y confunde la UX; además, las tablas con datos locales usan `getRenderAccionesDataSourceLocal` sin filtrar por permiso, a diferencia del listado que ya delega en `DatatableUtil.getRenderAcciones`.

## What Changes

- Alinear la columna **Actions** del listado principal para que solo muestre acciones permitidas: **View** (`detalle`) siempre que haya `ver`; **Edit** solo con `editar`; **Delete** solo con `eliminar` (sin duplicar un botón “ver” disfrazado de editar cuando ya existe detalle).
- Aplicar el mismo criterio en todas las tablas locales del wizard y formulario de Data Tracking (ítems, labor, materials, subcontractors, concrete vendors, attachments), usando `DatatableUtil.getAccionesDataSourceLocal` o construcción equivalente según el modo del formulario.
- Revisar botones de barra de herramientas ya condicionados en Twig (`New`, `Delete`, `Save`) y validar coherencia con JS (p. ej. botón bulk delete que se muestra/oculta por selección).
- Mantener **Export** y acciones de solo lectura (p. ej. descarga/vista de adjuntos) disponibles para usuarios con solo `ver`, salvo que el diseño indique lo contrario.
- No cambiar reglas de autorización en backend; el cambio es de UI coherente con permisos ya expuestos en `permiso` desde `index.html.twig`.

## Capabilities

### New Capabilities

_(ninguna — extensión de requisitos sobre field-data-tracking existente)_

### Modified Capabilities

- `field-data-tracking`: requisitos de visibilidad de acciones en listado y formulario admin según `permiso.agregar`, `permiso.editar`, `permiso.eliminar` y solo lectura con `ver`.

## Impact

- `public/assets/metronic8/js/pages/data-tracking.js` — listado principal y ~6 tablas locales.
- `public/assets/metronic8/js/utils/datatable-util.js` — posible uso/ampliación de `getAccionesDataSourceLocal` o `getRenderAccionesDataSourceLocal` con `permiso` (evaluar alcance mínimo).
- `templates/admin/data-tracking/index.html.twig` — verificación de botones ya envueltos en `{% if permiso.* %}`.
- Spec delta: `openspec/changes/fix-datatracking-permission-action-buttons/specs/field-data-tracking/spec.md` → merge a `openspec/specs/field-data-tracking/spec.md`.
