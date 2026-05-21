# Proyectos de construcción

## Requirements

### Requirement: Modelo de datos de proyecto

El sistema SHALL persistir el núcleo del dominio de obra mediante entidades incluyendo entre otras `Project`, `ProjectItem`, `ProjectStage`, `ProjectType`, `ProjectContact`, `ProjectCounty`, `ProjectConcreteClass`, `ProjectAttachment`, `ProjectNotes`, `ProjectPrevailingRole`, `ProjectPriceAdjustment`, `ProjectItemHistory`, según mapeo Doctrine en `src/Entity/`.

Además, cada proyecto MAY tener asociada **una** ubicación tipo **City** del catálogo `county`, persistida como FK nullable `project.city_id` → `county.county_id`, donde el registro referenciado representa una ciudad (columna `county.city` no vacía). Los condados del proyecto siguen gestionándose mediante `ProjectCounty` (relación N:M con registros de condado sin ciudad).

#### Scenario: Relación con catálogos

- GIVEN un proyecto
- WHEN se asocian condados, tipos, etapas o clases de hormigón
- THEN MUST existir tablas de unión o FKs coherentes con esas entidades

#### Scenario: Proyecto con ciudad opcional

- GIVEN un registro de ubicación tipo City en `county` (campo `city` poblado)
- WHEN un usuario guarda un proyecto seleccionando esa ciudad en el formulario admin
- THEN el sistema MUST persistir `project.city_id` apuntando a ese `county_id`
- AND al cargar el proyecto MUST devolver `city_id` y una descripción legible de la ciudad para la UI

#### Scenario: Proyecto sin ciudad

- GIVEN un proyecto existente o nuevo
- WHEN el usuario no selecciona ciudad
- THEN `project.city_id` MUST permanecer NULL
- AND el guardado del proyecto MUST completarse sin error

### Requirement: Formulario admin de proyecto — selector de ciudad

En el formulario admin de creación/edición de proyecto, el sistema SHALL mostrar un campo **City** (select simple) ubicado en la **misma fila** que el campo **County**, inmediatamente adyacente según el layout Bootstrap del módulo.

El selector MUST listar únicamente ubicaciones tipo City del catálogo administrado en `/admin/county` (registros con `city` no vacío). El selector de **County** MUST NOT incluir esos registros tipo City (solo condados / modo District).

#### Scenario: Guardar proyecto con county y city

- **WHEN** el usuario completa el formulario de proyecto, selecciona uno o más condados y opcionalmente una ciudad, y guarda
- **THEN** el sistema MUST persistir las filas en `project_county` para los condados elegidos
- **AND** MUST persistir `city_id` cuando se eligió ciudad

#### Scenario: Vista detalle muestra ciudad

- **WHEN** el usuario abre la vista detalle de un proyecto con `city_id` definido
- **THEN** MUST mostrarse el nombre de la ciudad en solo lectura junto al condado en la misma zona del formulario

#### Scenario: Carga de proyecto en edición

- **WHEN** el backend responde a `cargarDatos` de un proyecto con `city_id` persistido
- **THEN** el cliente MUST preseleccionar esa ciudad en el control `#city` (o id equivalente)

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

### Requirement: Log de proyecto (antes Notes)

El sistema SHALL gestionar entradas de seguimiento por proyecto mediante la entidad `ProjectNotes` (`project_notes`), presentadas en el admin como **Log** (no "Notes"). Cada entrada MUST incluir fecha, texto y referencia al proyecto; MUST asociar el usuario autenticado que creó o modificó por última vez la entrada (`project_notes.user_id` → `user.user_id`).

Las entradas del log MUST NOT eliminarse desde la UI ni mediante los endpoints admin de eliminación de notas de proyecto (`eliminarNotes`, `eliminarNotesDate`). La creación y edición MUST respetar los permisos existentes de edición del módulo Project.

#### Scenario: Usuario crea entrada de log

- **WHEN** un usuario con permiso de edición guarda una nueva entrada en el log del proyecto
- **THEN** el sistema MUST persistir `notes`, `date`, `project_id` y `user_id` del usuario autenticado
- **AND** el listado MUST mostrar la columna **User** con el nombre legible de ese usuario

#### Scenario: Usuario edita entrada de log

- **WHEN** un usuario con permiso de edición actualiza una entrada existente
- **THEN** el sistema MUST actualizar el contenido y fecha según el formulario
- **AND** MUST actualizar `user_id` al usuario que realizó la edición

#### Scenario: Listado sin eliminación

- **WHEN** un usuario abre la pestaña **Log** del proyecto (wizard o detalle)
- **THEN** la tabla MUST mostrar columnas Date, Log (contenido), User y Actions
- **AND** MUST NOT mostrar checkboxes de selección masiva ni botón de borrar por rango
- **AND** las acciones de fila MUST limitarse a editar o ver según permiso, sin acción delete

#### Scenario: Intento de eliminar por API admin

- **WHEN** un cliente invoca `eliminarNotes` o `eliminarNotesDate` del proyecto
- **THEN** el sistema MUST NOT borrar registros
- **AND** MUST responder con error indicando que la eliminación no está permitida

#### Scenario: Entrada histórica sin usuario

- **GIVEN** una fila en `project_notes` con `user_id` NULL (dato anterior a la migración)
- **WHEN** se lista el log del proyecto
- **THEN** la columna User MUST mostrarse vacía o con placeholder neutral sin fallar el listado

#### Scenario: Etiquetas de UI

- **WHEN** el usuario navega el módulo admin de proyectos
- **THEN** la pestaña, descripciones del wizard, encabezados de tabla y etiquetas del modal MUST usar el término **Log** en lugar de **Notes** para esta funcionalidad
