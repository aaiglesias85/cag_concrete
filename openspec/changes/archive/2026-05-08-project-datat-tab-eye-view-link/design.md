## Context

El listado de proyectos usa `DatatableUtil.getRenderAcciones` con la acción `detalle` para abrir la vista “solo ver” (`ProjectsDetalle` / formulario `#project-form-detalle`). Esa vista incluye el mismo asistente por pestañas que el modo edición, incluida la pestaña 11 (Data Tracking) con tabla `#data-tracking-table-editable-detalle` alimentada vía `project/listarDataTracking` en `projects-detalle.js`.

La pantalla completa de Data Tracking vive en `/admin/data-tracking`, con selector `#project` (select2) y lógica central en `data-tracking.js`. Ya existe un patrón de profundización vía `localStorage` (`data_tracking_id_edit`) al cargar la página.

## Goals / Non-Goals

**Goals:**

- En vista detalle, pestaña Data Tracking, mostrar un control con ícono de “ojo” que navegue a la pantalla admin de Data Tracking con el **mismo proyecto** ya seleccionado.
- En la misma tabla embebida, columna **Actions** por fila con acción **ver** (ojo) que abra el registro en el módulo Data Tracking en solo lectura (`data_tracking_id_view`).
- Reutilizar estilos y semántica de “ver” coherentes con el listado de proyectos (Metronic / ki-duotone).
- Implementación acotada a Twig + JS front; sin cambiar contratos JSON salvo que el query param requiera soporte en servidor (no esperado).

**Non-Goals:**

- Sustituir el listado embebido por un iframe o solo la pantalla completa sin tabla en proyecto.
- Rediseñar la pestaña ni el flujo de edición del proyecto.
- Cambiar permisos de Data Tracking ni añadir nuevos roles.

## Decisions

1. **Entrada contextual en Data Tracking: query string `project_id`**

   - **Rationale:** URLs con `?project_id=` son compartibles, depurables y no compiten con otras claves de `localStorage`. Es coherente con patrones HTTP habituales.
   - **Alternativa:** Solo `localStorage` (como `data_tracking_id_edit`) — evita parámetros visibles pero es opaca y más frágil si el usuario abre la URL en otra pestaña sin el mismo storage.

2. **Aplicar el proyecto al cargar `data-tracking.js`**

   - Tras `initSelectProject()` y el `change` handler registrado, leer `URLSearchParams` de `window.location.search`; si hay `project_id` numérico válido y existe `option[value=…]` en `#project`, hacer `$('#project').val(id).trigger('change')` (y refrescar select2 si hace falta).
   - Opcional: usar `history.replaceState` para quitar el query de la barra y evitar re-aplicar al refrescar (decisión en implementación; si se quita, documentar en tasks).

3. **Ubicación del ícono en vista detalle**

   - Colocar el enlace/botón en la barra de acciones/filtros de `#lista-data-tracking-detalle` (misma fila que filtros o bloque “Acciones”), con `href` apuntando a `/admin/data-tracking?project_id=<id>` donde `<id>` se toma de `#project_id_detalle` (o el hidden equivalente que ya usa el detalle).
   - Si el id no está disponible, no mostrar el control o deshabilitarlo (edge case de estado inconsistente).

4. **Permisos**

   - Mostrar el ícono solo cuando el usuario tenga capacidad de usar la pantalla Data Tracking de forma alineada con el resto del panel (reutilizar flags `permiso` / variables globales ya inyectadas en la plantilla de proyecto si existen; si no hay granularidad, mostrar siempre y dejar el firewall de Symfony devolver 403 — preferir alinear con permisos existentes en la página de proyectos).

5. **Columna Actions en tabla Data Tracking (vista detalle proyecto)**

   - **Problema actual:** `initTableDataTracking` en `projects-detalle.js` define 9 columnas de datos sin `{ data: null }` ni `columnDefs` final de acciones; el `<thead>` en Twig tampoco tiene `<th>Actions</th>`. En cambio `projects.js` sí añade columna de acciones con `getRenderAcciones(..., ['edit'])` y encabezado “Actions”.
   - **Solución:** Añadir `{ data: null }` y un `targets: -1` con `DatatableUtil.getRenderAcciones(data, type, row, permiso, ['detalle'])` (clase `a.detalle`, ícono ojo), más `<th>Actions</th>` en la plantilla.
   - **Clic:** En `#data-tracking-table-editable-detalle`, enlazar `a.detalle` al mismo flujo que `a.view`: `localStorage.setItem('data_tracking_id_view', id)` y `window.location.href = url_datatracking` (o un único delegado para `.view,.detalle` en esa tabla).

6. **Proyecto seleccionado al abrir detalle de un data tracking (solo lectura)**

   - El payload de `data-tracking/cargarDatos` ya incluye `project_id`. El formulario **edit** en `data-tracking.js` sincroniza `#project` al cargar; el flujo **detalle** (`data-tracking-detalle.js`) antes solo actualizaba textos del formulario detalle.
   - **Solución:** exponer `DataTracking.setProjectSelectFromId(projectId)` (mismo patrón `off` / `val` / `trigger('change')` / `on` que `cargarDatos` del formulario principal) y llamarlo desde `cargarDatos` del detalle cuando exista `project_id`, de modo que al cerrar el detalle o al usar filtros el listado quede alineado con el proyecto del registro visto.

## Risks / Trade-offs

- **[Riesgo]** El select `#project` en Data Tracking podría no incluir el proyecto (p. ej. filtros por compañía en el HTML). → **Mitigación:** verificar en Twig que el `<select id="project">` lista todos los proyectos elegibles; si no, valorar endpoint AJAX o ampliar opciones (fuera de alcance mínimo si ya lista todo).
- **[Riesgo]** Doble aplicación del `project_id` si el usuario recarga con el query aún presente. → **Mitigación:** `replaceState` tras aplicar o documentar como comportamiento aceptable.
- **[Trade-off]** Query visible expone el id interno del proyecto — aceptable en admin autenticado.

## Migration Plan

- Despliegue único de front (assets + Twig). No requiere migración de datos.
- **Rollback:** revertir commit; no hay estado persistente en BD.

## Open Questions

- ¿Debe el ícono abrir en la misma pestaña o en nueva pestaña (`target="_blank"`)? Por defecto: misma pestaña; el usuario puede pedir `_blank` en implementación.
