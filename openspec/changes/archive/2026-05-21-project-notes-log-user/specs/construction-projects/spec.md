## ADDED Requirements

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
