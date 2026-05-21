## Context

- **Modelo actual**: Cuatro entidades homogéneas (`ProjectAttachment`, `EstimateAttachment`, `InvoiceAttachment`, `DataTrackingAttachment`) con columnas `id`, `name`, `file` y FK al padre. No existe campo de texto libre.
- **UI actual**: Modales `#modal-archivo` (o variantes `-estimate`, `-invoice`) con pasos **Name** + **Attachment** (fileinput). La lista en memoria (`archivos[]`) transporta `id`, `name`, `file`, `posicion`; el guardado del padre serializa JSON y los servicios ejecutan `SalvarArchivos`.
- **Patrón de notas ricas**: Log/Notes de proyecto y notas de factura usan **Quill** vía `QuillUtil.init`, `getHtml`, `setHtml` sobre un `div` contenedor (p. ej. `#notes`), persistiendo HTML en columna `TEXT`.
- **Alcance transversal**: Projects (wizard + detalle), Estimates, Payments (adjuntos de factura), Invoices, Data Tracking (wizard + detalle). API móvil de proyecto devuelve `archivos` en payload completo.

## Goals / Non-Goals

**Goals:**

- Columna `note` (`TEXT NULL`) en las cuatro tablas de adjuntos y en todas las capas (entidad, listado, guardado, DTO).
- Campo **Note** en cada modal de adjunto con Quill, obligatorio u opcional según producto — **decisión: opcional** (nullable), igual que notas de log pueden estar vacías al crear.
- Contrato JSON unificado: cada entrada de `archivos` incluye `note` (string HTML o vacío).
- Misma sanitización/almacenamiento que `project_notes.notes` (sin nuevo stack de editor).

**Non-Goals:**

- Unificar las cuatro tablas en una sola tabla polimórfica de adjuntos.
- Adjuntos en otros módulos sin tabla `*_attachment` (p. ej. solo upload suelto sin entidad).
- Versionado o historial de cambios de la nota.
- i18n del label "Note" (se mantiene inglés como el resto del admin).

## Decisions

1. **Nombre de columna y API**: `note` (no `notes`) para distinguir del dominio Log/Notes y mantener singular por fila de adjunto. En JSON de cliente: propiedad `note`.

2. **Tipo y almacenamiento**: `TEXT NULL` en MySQL; Doctrine `type: 'text'`. Mismo tratamiento que `ProjectNotes::notes` — sin truncar en aplicación.

3. **Editor UI**: Reutilizar `QuillUtil` con contenedor dedicado por modal, p. ej. `#archivo-note` (projects/payments/data-tracking), `#archivo-note-estimate`, `#archivo-note-invoice`. Inicializar en `shown.bs.modal` del modal de archivo (o al abrir formulario) para evitar instancias huérfanas; destruir/limpiar al cerrar como en notes.

4. **Persistencia**: Extender bucles `SalvarArchivos` / actualización de nombre en cada servicio para `setNote()` desde el objeto del array. En listados (`ListarArchivosDe*`), añadir clave `note` en el array devuelto.

5. **Subida multipart (`salvarArchivo`)**: Hoy solo sube fichero y devuelve nombre en disco. **Decisión**: la `note` NO viaja en el POST de subida temporal; se guarda en el array en memoria del JS al confirmar el modal y se persiste en `SalvarArchivos` al guardar el padre (o al actualizar fila existente con `id`). Para edición de adjunto ya persistido, actualizar `note` en el mismo flujo que actualiza `name` (sin re-subir fichero si no cambia).

6. **Listado DataTable**: Añadir columna **Note** con preview truncado (texto plano vía strip HTML) o icono "view" que abra modal de solo lectura — **decisión**: columna opcional con tooltip/preview corto; detalle completo en modal de edición. Si la tabla queda ancha, priorizar preview en modal de edición y columna mínima (icono si hay contenido).

7. **SQL**: Un único archivo `database/2026_05_20_attachment_note.sql` con cuatro `ALTER TABLE ... ADD COLUMN note TEXT NULL` y comentarios.

8. **API móvil**: Incluir `note` en cada elemento de `archivos` en `ProjectService::ListarArchivosDeProject` / payload de detalle; sin editor en app salvo que ya exista — solo lectura en cliente móvil si consume el campo.

9. **Partial Twig compartido**: Evaluar `_attachment_modal.html.twig` — **decisión inicial**: duplicar bloque Quill en cada modal existente para minimizar riesgo de regresión en IDs JS; refactor a partial en follow-up opcional.

## Risks / Trade-offs

- **[Riesgo] HTML/XSS en `note`** → **Mitigación**: aplicar el mismo criterio que notas de proyecto al renderizar (escape en listados, `|raw` solo donde ya se hace para Quill).
- **[Riesgo] Olvidar un módulo** → **Mitigación**: checklist en `tasks.md` con los 5 JS + 5 twig + 4 entidades + API.
- **[Riesgo] Modal sin init Quill al editar** → **Mitigación**: `QuillUtil.setHtml` tras cargar datos en `initAccionesArchivo` / equivalente por página.
- **[Trade-off] Columna Note en tabla** puede ensanchar UI → preview truncado o indicador booleano "has note".

## Migration Plan

1. Ejecutar `database/2026_05_20_attachment_note.sql` en entornos (dev/staging/prod).
2. Desplegar backend + frontend en un solo release (columna nullable; compatible con clientes antiguos que ignoran `note`).
3. Rollback: revertir código; columna `note` puede permanecer sin uso.

## Open Questions

- ¿Mostrar columna Note en todas las DataTables de adjuntos o solo en modal de edición?
- ¿Validación mínima de longitud o contenido no vacío en `note`? (por defecto: ninguna, nullable).
