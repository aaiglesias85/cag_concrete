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

### Requirement: Visibilidad del texto completo en celdas truncadas (Invoices y Payments)

En las tablas DataTable de **líneas de ítem** de la vista de factura (`#items-table-editable`) y de la vista de pagos (`#payments-table-editable`), cuando el sistema muestre en la columna de ítem texto largo de forma visualmente truncada (p. ej. puntos suspensivos u omisión por recorte), el usuario MUST poder consultar el **texto completo** sin depender exclusivamente del hover del puntero sobre la celda.

El sistema MUST ofrecer un control en la misma celda (texto truncado y/o icono asociado) activable por **toque o clic** que abra una capa secundaria (p. ej. popover Bootstrap) con el texto íntegro escapado de forma segura. En dispositivos táctiles, esa activación MUST funcionar con un solo toque directo sobre el control previsto, sin requerir hover previo ni gesto de «mantener pulsado».

Cuando el texto no esté visualmente truncado, el sistema MUST NOT mostrar affordances engañosas de «ver más». Cuando sí esté truncado, MUST indicarse de forma perceptible que existe contenido adicional (p. ej. icono o estilo acordado en la implementación).

El comportamiento MUST reutilizar el mismo patrón técnico en `invoices.js` y `payments.js` (helper compartido en `datatable-util.js`), manteniendo la tabla legible y densa. Al abrir un popover de texto largo, cualquier otro popover del mismo tipo abierto MUST cerrarse. El usuario MUST poder cerrar el popover tocando fuera o activando otro control equivalente.

#### Scenario: Contenido largo en tabla de ítems de la factura

- **WHEN** el usuario visualiza una fila en la que el nombre del ítem aparece truncado en la columna correspondiente
- **THEN** MUST existir un control explícito en esa celda para abrir el texto íntegro
- **AND** ese control MUST NOT depender únicamente del hover del puntero

#### Scenario: Contenido largo en tabla de ítems de pagos

- **WHEN** el usuario visualiza una fila de la tabla de ítems de pagos en la que el texto del ítem aparece truncado
- **THEN** MUST existir la misma clase de control y comportamiento que en la tabla de ítems de la factura

#### Scenario: Interacción táctil en tablet

- **WHEN** el usuario usa un dispositivo táctil (p. ej. tablet) y el nombre del ítem está truncado
- **THEN** MUST poder leer el texto completo con un toque sobre el control de la celda
- **AND** el popover o capa secundaria MUST permanecer visible hasta que el usuario lo cierre o abra otro, sin exigir hover

#### Scenario: Texto no truncado

- **WHEN** el nombre del ítem cabe por completo en el ancho visible de la celda
- **THEN** MUST NOT mostrarse un icono o control de «ver más» que sugiera contenido oculto inexistente

#### Scenario: Cierre y una sola capa abierta

- **WHEN** el usuario abre el texto completo de un ítem y luego activa el control de otra fila truncada
- **THEN** MUST cerrarse el popover anterior
- **AND** MUST mostrarse solo el popover de la fila activada

### Requirement: Wizard de factura con pestaña de adjuntos

El sistema SHALL mostrar en el formulario administrativo de **Invoices** (wizard) una pestaña dedicada a **adjuntos** inmediatamente **después** de la pestaña **Items** y antes de cualquier otro paso posterior. La pestaña SHALL permitir listar, añadir (modal con nombre y fichero), editar metadatos mostrados, previsualizar o descargar según el patrón ya usado en **Payments**, y eliminar adjuntos individuales o en lote, respetando los permisos administrativos de la función **INVOICE** (no SHALL exigirse permiso de **PAYMENT** únicamente para subir o borrar desde esta pantalla).

#### Scenario: Estructura del wizard

- **WHEN** el usuario abre el formulario de creación o edición de factura en admin
- **THEN** el wizard MUST mostrar al menos las pestañas General, Items y Attachments en ese orden
- **AND** la pestaña Attachments MUST estar numerada/conectada como el paso que sigue a Items

#### Scenario: Carga de datos al editar

- **WHEN** el cliente solicita los datos de una factura existente para edición
- **THEN** la respuesta MUST incluir el arreglo `archivos` con entradas que contengan identificador persistido, nombre para mostrar, nombre de fichero en disco y orden, coherente con el contrato ya consumido por la pantalla de pagos

#### Scenario: Persistencia al guardar factura

- **WHEN** el usuario guarda una factura (alta o actualización) y la petición incluye la lista JSON de adjuntos
- **THEN** el sistema MUST sincronizar las filas `invoice_attachment` asociadas a ese `invoice_id` de forma coherente con la lógica existente de guardado de pagos (creación de nuevos enlaces y actualización de nombre/fichero cuando corresponda)

#### Scenario: Subida de fichero desde pantalla de factura

- **WHEN** un usuario con permiso de edición de facturas sube un fichero desde la pestaña Attachments
- **THEN** el sistema MUST aceptar la subida mediante un endpoint protegido por `FunctionId::INVOICE` (no solo por `FunctionId::PAYMENT`)
- **AND** el fichero MUST almacenarse bajo el directorio y convenciones ya usadas para adjuntos de factura (p. ej. `uploads/invoice/`)

#### Scenario: Documentación de base de datos

- **WHEN** se integra el cambio en el repositorio
- **THEN** MUST existir bajo la carpeta `database/` un archivo SQL de cambio que documente si hay migración de esquema; si no la hay, MUST indicarlo explícitamente en comentarios para operaciones

### Requirement: Adjuntos de factura y pago con nota enriquecida

El sistema SHALL persistir en `invoice_attachment` un campo `note` (texto nullable, HTML de editor rico) por cada adjunto. Las pantallas administrativas de **Payments** y **Invoices** (pestaña Attachments) MUST ofrecer campo **Note** con Quill en el modal de adjunto. El contrato JSON de `archivos` MUST incluir `note` en carga, guardado de factura y actualización de pago. El sistema MUST NOT persistir ni devolver adjuntos sin `file` válido.

#### Scenario: Contrato de datos en carga de factura

- **WHEN** el cliente solicita datos de factura para edición (wizard Invoices o Payments)
- **THEN** cada entrada de `archivos` MUST incluir `note` además de identificador, nombre, fichero y orden

#### Scenario: Guardado desde wizard de factura

- **WHEN** el usuario guarda una factura con adjuntos en la petición
- **THEN** la sincronización de `invoice_attachment` MUST persistir `note` para cada adjunto del payload con fichero válido

#### Scenario: Modal en pantalla de pagos

- **WHEN** el usuario añade o edita un adjunto desde la pestaña Attachments de Payments
- **THEN** el modal MUST capturar `note` vía Quill y enviarla en el arreglo en memoria hasta la persistencia en `ActualizarPayment` o equivalente

#### Scenario: Modal en pantalla de facturas

- **WHEN** el usuario añade o edita un adjunto desde la pestaña Attachments de Invoices
- **THEN** el modal MUST capturar `note` vía Quill con el mismo comportamiento que en Payments
