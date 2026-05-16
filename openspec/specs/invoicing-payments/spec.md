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

### Requirement: Acceso directo desde el listado de invoices a la edición de pagos

El sistema SHALL ofrecer en el listado administrativo principal de **Invoices** un control por fila (indicación visual de moneda, p. ej. símbolo `$`) en la columna **Actions** que navegue a la pantalla administrativa de **Payments** y abra el formulario de pagos asociado al `invoice_id` de esa fila, utilizando el mismo flujo de carga de datos que la acción de edición/visualización desde el listado de payments (`cargarDatos` con `invoice_id`).

El control MUST mostrarse solo si el usuario tiene el permiso administrativo necesario para usar la pantalla de pagos de forma coherente con las acciones ya disponibles allí (no exponer un enlace que sistemáticamente resulte en acceso denegado al abrir el formulario).

#### Scenario: Navegación con permiso y factura editable

- **WHEN** el usuario visualiza el listado de invoices y tiene permiso para acceder al formulario de pagos del invoice
- **AND** el invoice corresponde al caso en que el listado de payments permitiría editar pagos (no solo el modo restringido de solo lectura del listado)
- **THEN** al activar el control de moneda MUST navegarse a la vista de Payments con un identificador de invoice en la URL acordado por la implementación (p. ej. query `invoice_id`)
- **AND** MUST abrirse automáticamente el formulario de pagos de ese invoice con los datos cargados desde el backend de la misma forma que al editar desde el listado de payments

#### Scenario: Factura en estado de solo lectura en payments

- **WHEN** el usuario activa el control de moneda para un invoice que en el listado de payments se trata como cerrado o pagado de forma que solo se ofrece visualización
- **THEN** MUST abrirse el formulario en el modo solo lectura equivalente al ya implementado para ese estado en la pantalla de payments (sin ampliar permisos de edición)

#### Scenario: Sin permiso de payments

- **WHEN** el usuario solo tiene acceso al listado de invoices pero no al flujo de pagos según la política de permisos administrativa
- **THEN** MUST NOT mostrarse el control de acceso directo a pagos en la columna Actions

#### Scenario: Identificador de invoice inválido o error de carga

- **WHEN** la URL de deep link incluye un `invoice_id` que el backend no puede cargar para pagos
- **THEN** MUST mostrarse el mismo tipo de feedback de error que el resto de la pantalla de payments al fallar la carga
- **AND** MUST NOT dejar la interfaz en un estado inconsistente (p. ej. formulario parcialmente abierto sin datos válidos)
