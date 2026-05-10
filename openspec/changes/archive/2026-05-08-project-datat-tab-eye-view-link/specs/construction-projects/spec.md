## ADDED Requirements

### Requirement: Acceso a Data Tracking desde vista detalle del proyecto

En la interfaz admin de **vista detalle** (solo lectura) de un proyecto, el sistema SHALL mostrar en la pestaña de **Data Tracking** (orden 11 del asistente) un control con ícono de **visualización** (coherente con el ícono “solo ver” del listado de proyectos) que permita abrir la pantalla de gestión de **Data Tracking** del proyecto actualmente mostrado.

#### Scenario: Usuario en detalle con proyecto cargado

- **WHEN** el usuario tiene abierta la vista detalle de un proyecto y navega a la pestaña Data Tracking (11)
- **THEN** MUST mostrarse el control con ícono de ver vinculado al proyecto abierto
- **AND** al activar el control MUST navegarse a la experiencia de Data Tracking admin con ese proyecto como contexto (p. ej. URL con `project_id` o mecanismo equivalente documentado en `field-data-tracking`)
- **AND** el ícono de barra MUST NOT abrir por sí solo el detalle de un registro de data tracking (no hay fila asociada)

### Requirement: Columna Actions en Data Tracking (vista detalle de proyecto)

En la tabla embebida de Data Tracking del formulario **vista detalle** (`#data-tracking-table-editable-detalle`), el sistema SHALL mostrar una columna **Actions** con, como mínimo, una acción de **ver** (ícono ojo coherente con `DatatableUtil` / listado admin) por cada fila, que navegue al módulo admin de Data Tracking y abra ese **registro** en modo solo lectura (mismo mecanismo que `data_tracking_id_view` y la vista detalle del módulo).

#### Scenario: Fila con registro de data tracking

- **WHEN** el usuario ve el listado Data Tracking en la pestaña 11 del detalle de proyecto
- **THEN** MUST mostrarse la columna Actions con el control de ver por fila
- **AND** al activar el control MUST persistirse el identificador del data tracking para lectura (p. ej. `data_tracking_id_view`) y MUST navegarse a la pantalla de Data Tracking mostrando ese registro en modo lectura

#### Scenario: Paridad con modo edición del proyecto

- **WHEN** se compara la tabla Data Tracking en edición de proyecto y en vista detalle
- **THEN** la vista detalle MUST incluir columna de acciones por fila análoga en propósito (ver registro en el módulo), aunque en detalle no se requieran acciones de editar o eliminar salvo que el negocio las defina aparte

#### Scenario: Contexto de proyecto no disponible

- **WHEN** no es posible determinar de forma fiable el identificador del proyecto en vista detalle
- **THEN** el sistema MUST omitir el control o MUST mantenerlo deshabilitado sin navegación inválida
