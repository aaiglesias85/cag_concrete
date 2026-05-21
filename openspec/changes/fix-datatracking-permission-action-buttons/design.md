## Context

El módulo admin Data Tracking expone en Twig un objeto `permiso` con flags `agregar`, `editar`, `eliminar` (y acceso implícito con `ver` vía `AdminAccessService`). La plantilla `templates/admin/data-tracking/index.html.twig` ya oculta varios controles globales (`New Measurement`, bulk `Delete`, `Save` del wizard) según esos flags.

En JavaScript (`data-tracking.js`):

- El **listado principal** usa `DatatableUtil.getRenderAcciones(..., ['detalle', 'edit', 'delete'])`. `getRenderAcciones` muestra `delete` solo si `permiso.eliminar`, pero **siempre** incluye `edit` cuando está en el array; `_getRenderAccionEditar` cambia icono a “ojo” si no hay `editar`, lo que duplica la acción **View** (`detalle`) para usuarios solo lectura.
- Las **tablas locales** del formulario (ítems, labor, materials, subcontractors, concrete vendors, attachments) llaman `getRenderAccionesDataSourceLocal` con `['edit', 'delete']` fijo, sin pasar `permiso`.
- Existe el helper `DatatableUtil.getAccionesDataSourceLocal(mode, permiso)` (no usado aún en el repo) que codifica la regla: edit si `(new && agregar) || (edit && editar)`; delete si `(new && agregar) || (edit && eliminar)`.

Referencia de patrón correcto en el mismo codebase: `invoices.js` construye dinámicamente el array de acciones del listado según `permiso.editar` / `permiso.eliminar`.

## Goals / Non-Goals

**Goals:**

- Usuario con solo `ver`: ve **View** en el listado; no ve botones Edit/Delete (ni lápiz ni papelera) en listado ni tablas internas.
- Usuario con `editar` sin `eliminar`: puede editar filas/registros; no ve delete.
- Usuario con `agregar`: puede crear registro y, en modo “nuevo”, editar/eliminar filas locales según reglas de `getAccionesDataSourceLocal('new', permiso)`.
- Adjuntos: en solo lectura, conservar **download/view** sin edit/delete en fila.
- Comportamiento alineado entre listado server-side y tablas client-side del mismo módulo.

**Non-Goals:**

- Cambiar `PermisoUsuario`, voters Symfony o endpoints de guardado/eliminación (la autorización server-side ya debe rechazar operaciones no permitidas).
- Refactorizar otros módulos (projects, estimates, etc.) que repiten el patrón sin permiso en tablas locales.
- Rediseñar el flujo “view-only” vía `data_tracking_id_view` (solo coherencia de botones en la pantalla principal).

## Decisions

### 1. Listado principal: array de acciones dinámico (como invoices)

Construir `accionesListado` antes del `render` de la columna Actions:

- Siempre incluir `detalle` (requiere `ver`, ya garantizado por acceso al módulo).
- Incluir `edit` solo si `permiso.editar`.
- Incluir `delete` solo si `permiso.eliminar` (redundante con el check interno de `getRenderAcciones`, pero explícito y consistente con invoices).

**Alternativa descartada:** mantener `edit` y confiar en icono “ojo” — deja dos botones de vista y etiquetas confusas (“Edit” en tooltip de `_getRenderAccionEditar` cuando no hay editar).

### 2. Tablas locales: `getAccionesDataSourceLocal` + modo de formulario

Para cada `initTable*` local en `data-tracking.js`, reemplazar el array fijo por:

```javascript
DatatableUtil.getAccionesDataSourceLocal(formMode, permiso)
```

donde `formMode` es `'new'` o `'edit'` según si el usuario está creando o editando un data tracking (reutilizar la variable/estado que ya distingue nuevo vs edición en el módulo, p. ej. presencia de `data_tracking_id` o flag existente).

Para **adjuntos**, usar:

```javascript
const acciones = DatatableUtil.getAccionesDataSourceLocal(formMode, permiso);
if (permiso.ver || !acciones.length) { /* ver siempre permitido en módulo */ }
acciones.push('download'); // o siempre ['download'] si no hay edit/delete
```

Regla: `download` permanece para solo lectura; `edit`/`delete` solo según helper.

**Alternativa descartada:** extender `getRenderAccionesDataSourceLocal` con parámetro `permiso` en `datatable-util.js` — más invasivo cross-módulo; el helper `getAccionesDataSourceLocal` ya existe.

### 3. Sin cambios obligatorios en Twig salvo auditoría

Los botones `btn-nuevo-data-tracking`, `btn-eliminar-data-tracking`, `btn-wizard-finalizar` ya están condicionados. Tarea de verificación manual en tasks; no duplicar lógica en JS si el DOM no renderiza el botón.

### 4. Backend sin cambios

Confiar en permisos existentes en controlador; la UI es defensa en profundidad UX.

## Risks / Trade-offs

- **[Riesgo]** `formMode` incorrecto en tablas locales muestra edit/delete al abrir detalle en solo lectura → **Mitigación:** al abrir formulario solo lectura / detalle, forzar `formMode` que no conceda edit/delete; reutilizar el mismo flag que deshabilita campos o el flujo `data_tracking_id_view`.
- **[Riesgo]** Usuario con `editar` abre registro ajeno en modo view → **Mitigación:** ya cubierto por flujos existentes; no ampliar alcance.
- **[Trade-off]** No se unifica `getRenderAccionesDataSourceLocal` con permisos a nivel util global — otros módulos pueden seguir inconsistentes fuera de este change.

## Migration Plan

1. Implementar cambios en `data-tracking.js` (y uso de helper existente).
2. Probar matriz de permisos: solo ver; ver+editar; ver+editar+eliminar; ver+agregar.
3. Desplegar assets JS (cache bust habitual del proyecto).
4. Rollback: revertir commit JS/Twig si regresión visual.

## Open Questions

- ¿El formulario abierto desde **detalle** del listado (clase `.detalle`) debe tratarse como `formMode='edit'` con solo `ver` sin `editar`, o existe un modo `'view'` dedicado? → Implementación debe usar el estado que ya bloquea guardado en solo lectura.
