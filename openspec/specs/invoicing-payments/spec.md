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

### Requirement: Columnas monetarias del listado DataTable de Payments (admin)

En la pantalla admin de **Payments** (`/payment` y equivalente configurado por rutas), el DataTable del listado principal de invoices SHALL mostrar dos columnas monetarias contiguas: **Invoice** con el total facturado del invoice correspondiente en esa fila y **Payment Amount** con la suma de montos ya pagados en los ítems de ese mismo invoice.

El formato de presentación SHALL ser consistente con el resto de montos administrados en la aplicación (p. ej. separadores y decimales como en otros listados). El contenido MUST provenir del backend del listado (no valores inventados en cliente).

#### Scenario: Encabezados y valores visibles tras aplicar filtros

- **WHEN** el usuario aplicó filtros o búsqueda de forma que el DataTable muestra filas del listado de payments
- **THEN** MUST existir una columna titulada **Invoice** que muestre el monto total del invoice de esa fila
- **AND** MUST existir una columna **Payment Amount** inmediatamente a continuación con el total pagado agregado de los ítems de ese invoice
- **AND** MUST NOT usarse la etiqueta anterior «Amount» para la columna que representa solo el total del invoice (debe estar rotulada como **Invoice** o equivalente acordado en la misma semántica)

#### Scenario: Coherencia con detalle/edición del payment

- **WHEN** el mismo invoice se abre desde el listado para edición/visualización de líneas de pago
- **THEN** la suma mostrada como **Payment Amount** en la fila del listado MUST corresponder al agregado de pagos registrados por ítem según los mismos campos persistidos utilizados por el dominio de payment (paid amount por línea), sin desviaciones por redondeos distintos al cálculo servidor del listado
