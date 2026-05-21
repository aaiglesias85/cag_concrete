## Why

Las entradas de seguimiento en proyectos (`ProjectNotes`) se muestran como "Notes" y permiten borrado masivo o individual, lo que debilita la trazabilidad del historial de obra. El negocio necesita tratarlas como un **registro de log inmutable** con visibilidad de **quién** registró o modificó cada entrada.

## What Changes

- Renombrar en la UI del módulo de proyectos (pestaña, títulos, modales, botones y mensajes) la etiqueta **Notes** por **Log** (solo presentación; la tabla `project_notes` puede mantenerse).
- Agregar columna `user_id` (FK nullable a `user.user_id`) en `project_notes` y persistir el usuario autenticado al crear o actualizar una entrada.
- Mostrar una nueva columna **User** en el DataTable del log (nombre legible del usuario).
- Ocultar y deshabilitar la eliminación de entradas: quitar columna de checkboxes, acción delete en la tabla, botones de borrado por rango y cualquier acceso UI; no exponer delete en `ListarAccionesNotes` ni en el render de acciones del JS.
- Mantener creación y edición según permisos actuales del proyecto.

## Capabilities

### New Capabilities

- (ninguna; el comportamiento encaja en proyectos existentes)

### Modified Capabilities

- `construction-projects`: las notas de proyecto pasan a comportarse como log auditable (usuario visible, sin eliminación) y la UI las etiqueta como **Log**.

## Impact

- `database/` — script SQL `user_id` en `project_notes`.
- `src/Entity/ProjectNotes.php` — relación `ManyToOne` a `Usuario`.
- `src/Repository/ProjectNotesRepository.php` — join con usuario en listados.
- `src/Service/Admin/ProjectService.php` — `SalvarNotes` asigna usuario; `ListarNotes` devuelve `user`; `ListarAccionesNotes` sin delete; `EliminarNotes` / `EliminarNotesDate` sin uso desde UI (opcional: rechazar en backend).
- `src/Controller/Admin/ProjectController.php` — endpoints de listado (sin cambio de ruta).
- `templates/admin/project/index.html.twig` — pestaña Log, columna User, sin checkboxes/delete.
- `public/assets/metronic8/js/pages/projects.js` y `projects-detalle.js` — columnas DataTable, acciones solo edit/view, textos Log.
- Registros existentes: `user_id` NULL hasta que se editen o se ejecute backfill opcional.
