## Why

En el panel de proyectos, al abrir un proyecto con el ícono de “solo ver” (vista detalle), la pestaña 11 (Data Tracking) muestra el listado embebido pero no ofrece un atajo claro a la pantalla completa de Data Tracking de ese mismo proyecto. Eso obliga a navegar manualmente al módulo y buscar el proyecto, y rompe la paridad con el flujo de edición donde suele ser más evidente el trabajo en el módulo dedicado.

## What Changes

- En la **vista detalle** del proyecto (formulario `project-form-detalle`), pestaña **11 – Data Tracking**, mostrar un control con ícono de **ojo** (o equivalente visual al de “ver” ya usado en el listado de proyectos) que lleve al usuario a la **pantalla admin de Data Tracking** contextualizada al **proyecto actual**.
- **Corrección de paridad con modo edición:** la tabla `#data-tracking-table-editable-detalle` hoy **no** declara columna **Actions** ni columna `{ data: null }` en DataTables, a diferencia de `#data-tracking-table-editable` en `projects.js`. Debe añadirse una columna **Actions** por fila con ícono **ver** (mismo patrón que `DatatableUtil.getRenderAcciones` con acción `detalle`) que navegue al módulo Data Tracking y abra ese registro en **solo lectura** vía `localStorage` (`data_tracking_id_view`) y `data-tracking-detalle.js`, alineado con el comportamiento esperado del listado embebido.
- **Selector de proyecto en el módulo:** al abrir el detalle de un data tracking (vista solo lectura o equivalente), el selector `#project` de la pantalla principal del módulo MUST quedar con el **mismo** `project_id` que devuelve `cargarDatos`, igual que ya ocurre al cargar el formulario de edición en `data-tracking.js`.
- **Qué no hace el ícono de barra (alcance explícito):** no abre por sí solo el detalle de un día concreto (no hay fila asociada). El acceso **por registro** es el ojo en la **columna Actions** de cada fila (o el ícono de barra solo lleva al módulo con `project_id`).
- El enlace debe respetar permisos existentes (solo si el usuario ya puede acceder a Data Tracking / al proyecto de forma coherente con el resto del panel).
- Sin **BREAKING**: rutas y APIs actuales se extienden o se reutilizan; no se elimina el listado embebido en la pestaña.

## Capabilities

### New Capabilities

- _(ninguno; el comportamiento encaja en capacidades ya documentadas de proyectos y data tracking)_

### Modified Capabilities

- `construction-projects`: la UI admin de **vista detalle** de proyecto SHALL ofrecer en la pestaña Data Tracking (11) un acceso directo coherente (ícono “ver”) a la gestión de Data Tracking del proyecto abierto.
- `field-data-tracking`: la pantalla admin de listado/gestión de Data Tracking SHALL permitir **entrada contextual** desde el proyecto (p. ej. query `project_id` o patrón equivalente ya usado en el repo como `localStorage` para `data_tracking_id_edit`) para **preseleccionar** el proyecto al cargar.

## Impact

- Plantillas Twig: `templates/admin/project/index.html.twig` (panel de la pestaña Data Tracking en el formulario detalle).
- JavaScript: `public/assets/metronic8/js/pages/projects-detalle.js` (navegación o delegación del clic; columna Actions en DataTable detalle; `localStorage` `data_tracking_id_view` por fila).
- JavaScript: `public/assets/metronic8/js/pages/data-tracking.js` (lectura del parámetro o clave al iniciar, selección de `#project`, `setProjectSelectFromId`, refresco del listado si aplica).
- JavaScript: `public/assets/metronic8/js/pages/data-tracking-detalle.js` (sincronizar `#project` al cargar respuesta de detalle).
- Posible ajuste mínimo en ruta o controlador solo si hace falta pasar el id por servidor; preferir solución solo front + URL/localStorage si el select de proyectos ya está disponible en la página de Data Tracking.
