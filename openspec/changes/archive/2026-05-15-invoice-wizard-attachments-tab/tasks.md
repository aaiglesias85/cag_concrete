## 1. Base de datos y contrato

- [x] 1.1 Añadir en `database/` un script SQL (p. ej. `cambios_constructora_invoice_wizard_attachments_15_05.sql`) que documente con comentarios que no hay cambios de esquema porque `invoice_attachment` ya existe, o incluya ALTER solo si la revisión lo requiere.

## 2. Backend: carga y persistencia

- [x] 2.1 Extender `InvoiceService::CargarDatosInvoice` para incluir `archivos` mapeados desde `InvoiceAttachmentRepository::ListarAttachmentsDeInvoice` (mismo shape que `PaymentService::ListarArchivosDeInvoice`).
- [x] 2.2 Añadir campo `archivos` (string JSON opcional) a `InvoiceSalvarRequest` y `InvoiceActualizarRequest`; en `InvoiceController::salvar` / `actualizar`, decodificar y pasar a servicio.
- [x] 2.3 Tras persistir cabecera e ítems en `SalvarInvoice`, invocar la misma semántica que `PaymentService::SalvarArchivos` (extraer helper compartido o duplicar el bucle evitando dependencia circular con `PaymentService`).
- [x] 2.4 En `ActualizarInvoice`, sincronizar adjuntos de la misma forma antes del retorno exitoso.

## 3. Backend: subida y borrado con permiso INVOICE

- [x] 3.1 Registrar rutas en `src/Routes/Admin/invoice.yaml` para `salvarArchivo`, `eliminarArchivo`, `eliminarArchivos` (o nombres alineados con payment) apuntando a `InvoiceController`.
- [x] 3.2 Implementar acciones en `InvoiceController` con `#[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Edit|Delete, jsonOnDenied: true)]` delegando en la misma lógica de fichero que usa `PaymentService` (inyectar método compartido o servicio de ficheros sin crear ciclo).
- [x] 3.3 Reutilizar DTOs existentes de payment para borrar (`PaymentArchivoRequest` / `PaymentArchivosRequest`) o crear equivalentes bajo `Dto/Admin/Invoice/` si se prefiere separación.

## 4. Frontend: Twig y JavaScript

- [x] 4.1 En `templates/admin/invoice/index.html.twig`, añadir pestaña wizard **Attachments** después de Items y el `tab-pane` (tabla, botones, modal) tomando como referencia `payment/index.html.twig`, con IDs prefijados (`invoice`) para modal, tabla, botones y `fileinput`.
- [x] 4.2 En `public/assets/metronic8/js/pages/invoices.js`, aumentar `totalTabs` a 3; implementar estado `archivos`, DataTable/handlers (add/edit/delete/download), llamadas `POST` a rutas `invoice/...`, reset en `resetForms`, hidratación en `cargarDatos`, y `formData.set('archivos', JSON.stringify(archivos))` en el guardado junto al resto de campos.
- [x] 4.3 Ajustar `marcarPasosValidosWizard` / `validWizard` si debe poderse avanzar al tab 3 sin bloqueos inesperados.

## 5. Verificación

- [ ] 5.1 Probar alta de factura: subir adjunto tras guardar o según flujo acordado, recargar edición y comprobar lista.
- [ ] 5.2 Probar actualización: añadir, renombrar y eliminar adjuntos; verificar filas en BD y ficheros en `uploads/invoice/`.
- [ ] 5.3 Probar usuario con permiso INVOICE sin PAYMENT: subida y borrado desde pestaña nueva.

## 6. OpenSpec / spec canónica (post-implementación)

- [x] 6.1 Tras implementar, propagar el delta de `openspec/changes/invoice-wizard-attachments-tab/specs/invoicing-payments/spec.md` a `openspec/specs/invoicing-payments/spec.md` según el flujo de archivo del proyecto.
