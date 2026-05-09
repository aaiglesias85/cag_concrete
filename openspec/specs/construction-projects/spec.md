# Proyectos de construcción

## Requirements

### Requirement: Modelo de datos de proyecto

El sistema SHALL persistir el núcleo del dominio de obra mediante entidades incluyendo entre otras `Project`, `ProjectItem`, `ProjectStage`, `ProjectType`, `ProjectContact`, `ProjectCounty`, `ProjectConcreteClass`, `ProjectAttachment`, `ProjectNotes`, `ProjectPrevailingRole`, `ProjectPriceAdjustment`, `ProjectItemHistory`, según mapeo Doctrine en `src/Entity/`.

#### Scenario: Relación con catálogos

- GIVEN un proyecto
- WHEN se asocian condados, tipos, etapas o clases de hormigón
- THEN MUST existir tablas de unión o FKs coherentes con esas entidades

### Requirement: Controlador y rutas admin de proyecto

El sistema SHALL exponer operaciones de gestión de proyectos vía `App\Controller\Admin\ProjectController` y rutas definidas en `src/Routes/Admin/project.yaml` (prefijo `/admin`).

**Pendiente de confirmar:** lista exhaustiva de acciones (crear, editar, listar, ítems, contactos, archivos, etc.) y reglas de negocio por acción sin leer el controlador completo línea a línea.

### Requirement: API móvil de listado y detalle

El sistema SHALL exponer bajo `/api/{lang}/project` (con `lang` es|en) al menos:

- `GET /{lang}/project/listar` → `App\Controller\App\ProjectController::listar`
- `GET /{lang}/project/cargarDatos` → `App\Controller\App\ProjectController::cargarDatos`

ambas con autenticación API estándar (roles en `access_control` para `^/api`).

#### Scenario: Cliente autenticado

- GIVEN un token JWT válido
- WHEN el cliente llama a listar o cargar datos
- THEN MUST obtenerse JSON con la forma definida en DTOs de respuesta bajo `src/Dto/Api/Response/Project/`

### Requirement: Scripts de mantenimiento relacionados

El sistema SHALL invocar desde `ScriptController` lógica en `ScriptService` que puede afectar derivados de proyecto/ítem (p. ej. `definiritemprincipal`, enlaces subcontractor/datatracking); el detalle algorítmico MUST tomarse de `ScriptService` y documentación existente.

**Pendiente de confirmar:** efectos secundarios exactos de cada script sobre tablas de proyecto.

### Requirement: Acceso a Data Tracking desde vista detalle del proyecto

En la interfaz admin de **vista detalle** (solo lectura) de un proyecto, el sistema SHALL mostrar en la pestaña de **Data Tracking** (orden 11 del asistente) un control con ícono de **visualización** (coherente con el ícono “solo ver” del listado de proyectos) que permita abrir la pantalla de gestión de **Data Tracking** del proyecto actualmente mostrado.

#### Scenario: Usuario en detalle con proyecto cargado

- **WHEN** el usuario tiene abierta la vista detalle de un proyecto y navega a la pestaña Data Tracking (11)
- **THEN** MUST mostrarse el control con ícono de ver vinculado al proyecto abierto
- **AND** al activar el control MUST navegarse a la experiencia de Data Tracking admin con ese proyecto como contexto (p. ej. URL con `project_id` o mecanismo equivalente documentado en la spec de field-data-tracking)
- **AND** el ícono de barra MUST NOT abrir por sí solo el detalle de un registro de data tracking (no hay fila asociada)

#### Scenario: Contexto de proyecto no disponible

- **WHEN** no es posible determinar de forma fiable el identificador del proyecto en vista detalle
- **THEN** el sistema MUST omitir el control o MUST mantenerlo deshabilitado sin navegación inválida

### Requirement: Columna Actions en Data Tracking (vista detalle de proyecto)

En la tabla embebida de Data Tracking del formulario **vista detalle** (`#data-tracking-table-editable-detalle`), el sistema SHALL mostrar una columna **Actions** con, como mínimo, una acción de **ver** (ícono ojo coherente con `DatatableUtil` / listado admin) por cada fila, que navegue al módulo admin de Data Tracking y abra ese **registro** en modo solo lectura (mismo mecanismo que `data_tracking_id_view` y la vista detalle del módulo).

#### Scenario: Fila con registro de data tracking

- **WHEN** el usuario ve el listado Data Tracking en la pestaña 11 del detalle de proyecto
- **THEN** MUST mostrarse la columna Actions con el control de ver por fila
- **AND** al activar el control MUST persistirse el identificador del data tracking para lectura (p. ej. `data_tracking_id_view`) y MUST navegarse a la pantalla de Data Tracking mostrando ese registro en modo lectura

#### Scenario: Paridad con modo edición del proyecto

- **WHEN** se compara la tabla Data Tracking en edición de proyecto y en vista detalle
- **THEN** la vista detalle MUST incluir columna de acciones por fila análoga en propósito (ver registro en el módulo), aunque en detalle no se requieran acciones de editar o eliminar salvo que el negocio las defina aparte
