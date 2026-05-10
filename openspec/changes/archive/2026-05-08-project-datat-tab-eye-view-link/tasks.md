## 1. Vista detalle — UI del acceso “ojo” a Data Tracking

- [x] 1.1 En `templates/admin/project/index.html.twig`, dentro del bloque `#lista-data-tracking-detalle` (pestaña 11 detalle), añadir un enlace o botón con ícono `ki-eye` (o equivalente al de acción detalle del datatable de proyectos), apuntando a `/admin/data-tracking?project_id=` + valor dinámico del id del proyecto en vista detalle (p. ej. campo hidden ya usado por `#project-form-detalle`).
- [x] 1.2 Ajustar layout (flex/gap) para que el ícono quede alineado con filtros o en el bloque de acciones sin romper responsive.
- [x] 1.3 Si existe variable Twig de permisos para Data Tracking, envolver el control con la misma condición; si no hay granularidad documentada, dejar visible y confiar en el firewall (documentar en comentario mínimo solo si el equipo lo exige).

## 2. Pantalla Data Tracking — preselección por `project_id`

- [x] 2.1 En `public/assets/metronic8/js/pages/data-tracking.js`, tras inicializar el select de proyecto (`initSelectProject` y registro de `change`), leer `project_id` desde `window.location.search`.
- [x] 2.2 Si el valor existe y hay `option[value="…"]` en `#project`, asignar valor, actualizar select2 y disparar `change` para reutilizar `changeProject` / carga de ítems.
- [x] 2.3 Si el parámetro es inválido o no hay opción, no alterar el estado actual (comportamiento actual de página).
- [x] 2.4 Opcional según decisión de UX: usar `history.replaceState` para eliminar `project_id` de la URL tras aplicar, evitando re-aplicación al refrescar.

## 3. Verificación manual

- [x] 3.1 Abrir un proyecto en modo “solo ver”, ir a pestaña 11 Data Tracking, pulsar el ícono de **barra** y confirmar que `/admin/data-tracking` abre con el proyecto correcto seleccionado **sin** abrir por sí solo el detalle de un día/registro (solo listado + proyecto en el select).
- [x] 3.2 Confirmar que el ojo por **fila** (columna Actions) del listado embebido lleva al módulo y abre el registro en modo ver vía `data_tracking_id_view`.
- [x] 3.3 Probar sin permisos o con id inexistente según política del panel (403 o lista sin opción) y confirmar ausencia de errores JS.

## 4. Columna Actions por fila (vista detalle Data Tracking)

- [x] 4.1 Añadir `<th>Actions</th>` a `#data-tracking-table-editable-detalle` en `templates/admin/project/index.html.twig`.
- [x] 4.2 En `projects-detalle.js`, ampliar `initTableDataTracking` con columna `{ data: null }` y `columnDefs` `targets: -1` usando `DatatableUtil.getRenderAcciones(data, type, row, permiso, ['detalle'])` (u ojo equivalente si `permiso` no aplica a `detalle`).
- [x] 4.3 Enlazar `a.detalle` en `#data-tracking-table-editable-detalle` al mismo flujo que `a.view` (`data_tracking_id_view` + `url_datatracking`).

## 5. Proyecto en selector al abrir detalle de data tracking

- [x] 5.1 En `data-tracking.js`, exponer `DataTracking.setProjectSelectFromId` reutilizando el patrón de `cargarDatos` (desactivar `change`, `val`, `trigger('change')`, reactivar).
- [x] 5.2 En `data-tracking-detalle.js`, tras `cargarDatos` del API, llamar a ese helper cuando venga `project_id` en el payload.
