## ADDED Requirements

### Requirement: Adjuntos de factura y pago con nota enriquecida

El sistema SHALL persistir en `invoice_attachment` un campo `note` (texto nullable, HTML de editor rico) por cada adjunto. Las pantallas administrativas de **Payments** y **Invoices** (pestaña Attachments) MUST ofrecer campo **Note** con Quill en el modal de adjunto. El contrato JSON de `archivos` MUST incluir `note` en carga, guardado de factura y actualización de pago.

#### Scenario: Contrato de datos en carga de factura

- **WHEN** el cliente solicita datos de factura para edición (wizard Invoices o Payments)
- **THEN** cada entrada de `archivos` MUST incluir `note` además de identificador, nombre, fichero y orden

#### Scenario: Guardado desde wizard de factura

- **WHEN** el usuario guarda una factura con adjuntos en la petición
- **THEN** la sincronización de `invoice_attachment` MUST persistir `note` para cada adjunto del payload

#### Scenario: Modal en pantalla de pagos

- **WHEN** el usuario añade o edita un adjunto desde la pestaña Attachments de Payments
- **THEN** el modal MUST capturar `note` vía Quill y enviarla en el arreglo en memoria hasta la persistencia en `ActualizarPayment` o equivalente

#### Scenario: Modal en pantalla de facturas

- **WHEN** el usuario añade o edita un adjunto desde la pestaña Attachments de Invoices
- **THEN** el modal MUST capturar `note` vía Quill con el mismo comportamiento que en Payments
