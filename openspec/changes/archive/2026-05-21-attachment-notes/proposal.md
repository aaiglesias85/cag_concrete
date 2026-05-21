## Why

Los adjuntos en el sistema solo guardan nombre y fichero (`name`, `file`). No hay contexto textual rico asociado a cada documento, lo que obliga a los usuarios a usar notas separadas o comunicación externa para explicar qué es cada archivo. El negocio necesita una **nota con formato** por adjunto, con la misma experiencia que las entradas de Log/Notes (editor Quill), en **todos** los flujos donde se suben o editan attachments.

## What Changes

- Añadir columna `note` (`TEXT`, nullable) en las cuatro tablas de adjuntos: `project_attachment`, `estimate_attachment`, `invoice_attachment`, `data_tracking_attachment`, con script SQL en `database/`.
- Extender entidades Doctrine, DTOs de request/response, servicios (`SalvarArchivos`, listados, actualización de nombre) y endpoints de subida/guardado para persistir y devolver `note` (HTML sanitizado o almacenado como en `project_notes.notes`).
- En cada modal/panel de **New/Edit Attachment** (Projects, Estimates, Payments, Invoices, Data Tracking): campo **Note** con editor Quill (`QuillUtil`), lectura/escritura al crear y editar, y visualización en listado o detalle cuando aplique.
- Incluir `note` en el JSON de `archivos` enviado al guardar wizard/formulario principal y en respuestas de carga (`Listar*`, `cargarDatos`, API móvil de proyecto si expone `archivos`).
- Alinear contrato de datos en JS (`archivos[]`) con propiedad `note` en todos los `*.js` de páginas que gestionan adjuntos.

## Capabilities

### New Capabilities

- _(ninguna; el comportamiento encaja en capacidades de dominio existentes)_

### Modified Capabilities

- `construction-projects`: adjuntos de proyecto con campo `note` editable (Quill) en alta/edición y en API/listados.
- `estimates`: adjuntos de estimate con campo `note` en modal y persistencia.
- `invoicing-payments`: adjuntos de factura y pago (`InvoiceAttachment`) con campo `note` en pestañas Attachments de Invoice y Payment.
- `field-data-tracking`: adjuntos de data tracking con campo `note` en wizard y detalle.
- `admin-panel`: modales de incorporación de archivo MUST incluir el editor de nota con el mismo patrón Quill que Log/Notes.

## Impact

- **Base de datos**: `database/2026_05_20_attachment_note.sql` (o nombre fechado equivalente) — `ALTER TABLE` en las cuatro tablas.
- **PHP**: `ProjectAttachment`, `EstimateAttachment`, `InvoiceAttachment`, `DataTrackingAttachment`; `ProjectService`, `EstimateService`, `InvoiceService`, `PaymentService`, `DataTrackingService`; controladores Admin y DTOs bajo `Dto/Admin/*/`.
- **Twig**: modales `#modal-archivo*` en `project`, `estimate`, `payment`, `invoice`, `data-tracking` (y detalle si duplica modal).
- **JS**: `projects.js`, `projects-detalle.js`, `estimates.js`, `payments.js`, `invoices.js`, `data-tracking-detalle.js` (y `data-tracking` index si aplica).
- **API móvil**: `ProjectController` / payload de proyecto — incluir `note` en `archivos` si el cliente consume adjuntos.
- Registros existentes: `note` NULL hasta edición manual.
