## Context

El módulo admin de proyectos incluye la pestaña **Notes** (ítem 13 del wizard) con DataTable `#notes-table-editable`, modal de alta/edición y endpoints `listarNotes`, `salvarNotes`, `eliminarNotes`, `eliminarNotesDate`. La entidad `ProjectNotes` (`project_notes`) guarda `notes`, `date` y `project_id` sin referencia al usuario. Las acciones de fila se generan en `ProjectService::ListarAccionesNotes` y en `projects.js` vía `DatatableUtil.getRenderAcciones` con `['edit', 'delete']`. El borrado masivo depende de `permiso.eliminar` y del botón `#btn-eliminar-notes`.

## Goals / Non-Goals

**Goals:**

- Etiquetar la funcionalidad como **Log** en toda la UI del proyecto (wizard, tablas, modales, mensajes de confirmación visibles al usuario).
- Persistir `user_id` en cada guardado (`SalvarNotes`) con el usuario autenticado.
- Exponer nombre de usuario en el listado (`ListarNotes`).
- Eliminar toda vía de borrado en UI (fila, bulk, botón por rango de fechas, columna checkbox).
- Rechazar eliminación en backend para endpoints de proyecto (respuesta de error clara) para evitar bypass.

**Non-Goals:**

- Renombrar tabla `project_notes`, clase `ProjectNotes` o rutas HTTP (`listarNotes`, etc.).
- Cambiar notas en otros módulos (payments, subcontractors, invoices).
- Backfill masivo de `user_id` en registros históricos (opcional manual; NULL se muestra como vacío o "—").
- Permisos nuevos de rol; se reutiliza `editar` del proyecto.

## Decisions

### 1. Columna `user_id` en `project_notes`

- **Decisión:** `user_id INT NULL`, FK a `user(user_id)`, índice, mapeo `ManyToOne` a `Usuario` en `ProjectNotes`.
- **Comportamiento:** En cada `SalvarNotes` (alta o edición), asignar `$entity->setUser($usuarioActual)`. Refleja el usuario que realizó el último cambio.
- **Alternativa descartada:** Tabla de historial separada — exceso de alcance para el requisito.

### 2. Presentación del usuario en listado

- **Decisión:** Campo JSON `user` en `ListarNotes`: concatenación `nombre + apellidos` de `Usuario`, o cadena vacía si `user_id` es NULL.
- **Alternativa:** Solo `email` — menos legible en pantalla de obra.

### 3. Deshabilitar eliminación

- **Decisión (UI):** Quitar `permiso.eliminar` del bloque de notes en Twig (checkbox header, `#btn-eliminar-notes`), quitar `delete` de `getRenderAcciones`, eliminar handlers JS de delete/bulk en `projects.js` y `projects-detalle.js`.
- **Decisión (backend):** `EliminarNotes` y `EliminarNotesDate` en `ProjectController` devuelven JSON `{ success: false, error: '...' }` sin borrar; `ListarAccionesNotes` no emite enlace delete.
- **Alternativa descartada:** Dejar endpoints activos sin UI — riesgo de llamadas directas.

### 4. Renombrado UI "Notes" → "Log"

- **Decisión:** Solo strings visibles en `index.html.twig` (pestaña wizard, encabezados de tabla, modal labels, `wizard-desc`) y textos en JS del módulo proyecto. Mantener ids internos (`notes-table-editable`, `notes-form`) para minimizar diff.
- **Log de auditoría Symfony:** Actualizar categoría de `SalvarLog` de `Project Notes` a `Project Log` en operaciones de esta entidad.

## Risks / Trade-offs

- **[Registros antiguos sin usuario]** → Mostrar celda User vacía o "—"; se actualiza al editar la entrada.
- **[Confusión con entidad `Log` del sistema]** → Solo etiqueta UI; la tabla sigue siendo `project_notes`.
- **[API o integraciones que llamen eliminarNotes]** → Respuesta explícita de rechazo; documentar en release notes si existiera consumidor externo.

## Migration Plan

1. Ejecutar script SQL en `database/` (p. ej. `2026_05_20_project_notes_user_id.sql`): `ALTER TABLE project_notes ADD COLUMN user_id ...`, FK e índice.
2. Desplegar código PHP + assets + Twig.
3. Limpiar caché Symfony si aplica.
4. **Rollback:** quitar columna solo si no hay dependencia; revertir código restaura delete (no recomendado tras go-live).

## Open Questions

- Ninguna crítica; backfill histórico de `user_id` queda a criterio del negocio (fuera de alcance).
