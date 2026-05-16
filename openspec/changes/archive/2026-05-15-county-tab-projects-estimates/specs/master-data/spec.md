## ADDED Requirements

### Requirement: Pantalla admin de county — proyectos y estimados asociados

En la experiencia admin de **Locations / County** (`/county`), el sistema SHALL proporcionar, al **editar** un county persistido, una **pestaña adicional** (además de la información general) que muestre las listas de **proyectos** vinculados a ese county mediante la relación persistida correspondiente (p. ej. `project_county` / repositorio equivalente) y de **estimados** cuyo `county_id` coincida con el county editado.

#### Scenario: Edición de county con asociaciones

- **WHEN** un usuario autorizado abre el formulario de edición de un county existente y el backend devuelve los datos de carga (`cargarDatos` o equivalente) incluyendo las colecciones serializadas de proyectos y estimados asociados
- **THEN** MUST mostrarse la pestaña de proyectos y estimados con el mismo patrón de navegación por pestañas usado en otras pantallas admin de maestros que listan proyectos relacionados (p. ej. item, concrete class)
- **AND** MUST poder consultarse en tablas las filas correspondientes con capacidad de búsqueda en cliente acorde a la implementación existente en esas pantallas

#### Scenario: Navegación al detalle de proyecto

- **WHEN** el usuario activa la acción de detalle/abrir proyecto desde la lista de proyectos asociados al county
- **THEN** el sistema MUST llevar al usuario al flujo admin de proyecto ya establecido en el panel (mismo mecanismo que en las pantallas de referencia que abren proyecto desde una lista relacionada)

#### Scenario: Navegación al detalle de estimado

- **WHEN** el usuario activa la acción de detalle/abrir estimado desde la lista de estimados asociados al county
- **THEN** el sistema MUST llevar al usuario al flujo admin de estimados ya establecido en el panel (mismo mecanismo que en `estimates.js` para abrir un estimado en edición)

#### Scenario: County sin vínculos

- **WHEN** el county no tiene proyectos ni estimados asociados
- **THEN** las tablas correspondientes MUST mostrarse vacías sin error
- **AND** MUST seguir pudiéndose guardar el formulario general del county
