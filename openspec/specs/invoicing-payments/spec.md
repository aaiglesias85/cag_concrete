# Facturación, pagos y override payment

## Requirements

### Requirement: Modelo de facturación

El sistema SHALL persistir facturas y líneas mediante entidades `Invoice`, `InvoiceItem`, `InvoiceAttachment`, `InvoiceNotes`, `InvoiceItemNotes`, y estructuras de historial/unpaid relacionadas (`InvoiceItemUnpaidQtyHistory`, etc.) según `src/Entity/`.

### Requirement: Pagos y notas de pago

El sistema SHALL gestionar pagos a través de `App\Controller\Admin\PaymentController` y DTOs bajo `Dto/Admin/Payment/` (listados, notas, archivos, cambio de estado).

**Pendiente de confirmar:** máquina de estados completa de pagos y validaciones por moneda/fecha.

### Requirement: Override payment

El sistema SHALL soportar cabeceras `InvoiceOverridePayment` y líneas `InvoiceItemOverridePayment` con historiales de cantidades pagadas/no pagadas (`InvoiceItemOverridePaymentPaidQtyHistory`, `InvoiceItemOverridePaymentUnpaidQtyHistory`) y resolvers dedicados (p. ej. `InvoicePaidQtyOverrideResolver`, `InvoiceUnpaidQtyOverrideResolver`) usados desde `InvoiceService` y `ProjectService`.

#### Scenario: Cálculo efectivo de cantidades

- GIVEN facturas e ítems de proyecto con posibles overrides
- WHEN se listan ítems de factura o datos de proyecto para facturación
- THEN el sistema MUST aplicar las reglas de cantidades efectivas descritas en el código y en `docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md` / `docs/OVERRIDE_PAID_QTY.md`

**Pendiente de confirmar:** duplicar aquí el texto normativo completo de encadenamiento unpaid/paid; para OpenSpec futuro conviene enlazar escenarios de negocio desde estas specs a extractos verificables en tests.

### Requirement: Exportación de facturas

El sistema SHALL incluir en `App\Service\Admin\InvoiceService` uso de **PhpSpreadsheet** para exportaciones (estilos, celdas, etc.).

**Pendiente de confirmar:** formatos soportados (xlsx/csv) y permisos requeridos en admin.

### Requirement: Rutas admin

El sistema SHALL definir rutas de factura y override en `src/Routes/Admin/invoice.yaml` y `override_payment.yaml` hacia los controladores correspondientes.
