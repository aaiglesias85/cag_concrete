## MODIFIED Requirements

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
