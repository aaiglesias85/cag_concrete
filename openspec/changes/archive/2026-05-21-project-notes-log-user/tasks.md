## 1. Base de datos y entidad

- [x] 1.1 Crear script SQL `database/2026_05_20_project_notes_user_id.sql` con columna `user_id`, FK a `user(user_id)` e índice
- [x] 1.2 Añadir relación `ManyToOne` a `Usuario` en `ProjectNotes` (`user_id`) con getter/setter

## 2. Backend

- [x] 2.1 En `ProjectNotesRepository`, incluir join/fetch de usuario en consultas de listado
- [x] 2.2 En `SalvarNotes`, asignar usuario autenticado en alta y edición; actualizar categoría de log a `Project Log`
- [x] 2.3 En `ListarNotes`, devolver campo `user` (nombre legible) en cada fila
- [x] 2.4 En `ListarAccionesNotes`, eliminar enlace HTML de delete (solo edit/view)
- [x] 2.5 En `EliminarNotes` y `EliminarNotesDate`, rechazar operación con `success: false` sin borrar filas

## 3. UI Twig (proyecto admin)

- [x] 3.1 Renombrar etiquetas Notes → Log en pestaña wizard, `wizard-desc`, encabezados de tabla y modal (formulario y detalle)
- [x] 3.2 Añadir columna `<th>User</th>` en tablas `#notes-table-editable` y `#notes-table-editable-detalle`
- [x] 3.3 Quitar columna checkbox, botón `#btn-eliminar-notes` y bloques condicionados a `permiso.eliminar` en la sección de log

## 4. JavaScript

- [x] 4.1 En `projects.js`: columna `user`, quitar checkbox/delete/bulk handlers, acciones solo `edit` (o `view`), ajustar `order` de columnas, textos Log
- [x] 4.2 En `projects-detalle.js`: mismos cambios para tabla y acciones de log en vista detalle
- [x] 4.3 Actualizar mensajes de confirmación visibles que digan "notes" por "log entry" donde aplique

## 5. Calidad

- [x] 5.1 Ejecutar `composer cs-fix` y `composer phpstan`; corregir errores nuevos
- [x] 5.2 Verificar manualmente: crear log, editar (user actualizado), listado con User, sin botones de eliminar
