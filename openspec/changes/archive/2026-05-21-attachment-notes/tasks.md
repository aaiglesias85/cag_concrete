## 1. Base de datos y entidades

- [x] 1.1 Crear `database/2026_05_20_attachment_note.sql` con `ADD COLUMN note TEXT NULL` en `project_attachment`, `estimate_attachment`, `invoice_attachment`, `data_tracking_attachment`
- [x] 1.2 Añadir propiedad `note` (getter/setter) en `ProjectAttachment`, `EstimateAttachment`, `InvoiceAttachment`, `DataTrackingAttachment`

## 2. Backend — persistencia y API

- [x] 2.1 Extender `ListarArchivosDe*` y `SalvarArchivos` en `ProjectService` para leer/escribir `note`
- [x] 2.2 Extender listado/guardado de adjuntos en `EstimateService`
- [x] 2.3 Extender listado/guardado de adjuntos en `InvoiceService` y `PaymentService` (`ListarArchivosDeInvoice`, `SalvarArchivos`)
- [x] 2.4 Extender listado/guardado de adjuntos en `DataTrackingService`
- [x] 2.5 Actualizar DTOs de request que transportan `archivos` (Project, Estimate, Invoice, Payment, DataTracking) si validan propiedades explícitas
- [x] 2.6 Incluir `note` en payload de API móvil de proyecto (`ListarArchivosDeProject` / `ProjectDetailPayload` si aplica)

## 3. UI Twig — modales de adjunto

- [x] 3.1 Añadir bloque Quill **Note** en `templates/admin/project/index.html.twig` (`#modal-archivo`)
- [x] 3.2 Añadir bloque Note en `templates/admin/estimate/index.html.twig` (`#modal-archivo-estimate`)
- [x] 3.3 Añadir bloque Note en `templates/admin/payment/index.html.twig` y `templates/admin/invoice/index.html.twig`
- [x] 3.4 Añadir bloque Note en `templates/admin/data-tracking/index.html.twig` (y detalle si duplica modal)

## 4. Frontend JS

- [x] 4.1 `projects.js` y `projects-detalle.js`: init Quill, get/set `note` en alta/edición de adjunto, serializar en `archivos[]`
- [x] 4.2 `estimates.js`: mismo patrón para adjuntos de estimate
- [x] 4.3 `payments.js` e `invoices.js`: mismo patrón para adjuntos de factura
- [x] 4.4 `data-tracking-detalle.js` (y JS de index si aplica): mismo patrón
- [x] 4.5 Opcional: columna o indicador de nota en DataTable de adjuntos por módulo

## 5. Calidad y cierre

- [x] 5.1 Ejecutar `composer cs-fix` y `composer phpstan`
- [x] 5.2 Verificar manualmente alta/edición de adjunto con nota en Projects, Estimates, Payments, Invoices y Data Tracking
- [x] 5.3 Verificar que registros antiguos sin `note` cargan sin error (null/vacío)
