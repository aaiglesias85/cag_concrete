## ADDED Requirements

### Requirement: Visibilidad del texto completo en celdas truncadas (Invoices y Payments)

En las tablas DataTable de **líneas de ítem** de la vista de factura (`#items-table-editable`) y de la vista de pagos (`#payments-table-editable`), cuando el sistema muestre en la columna de ítem texto largo de forma visualmente truncada (p. ej. puntos suspensivos u omisión por recorte), el usuario MUST poder consultar el **texto completo** sin depender exclusivamente del hover del puntero sobre la celda (p. ej. tooltips que solo responden a hover).

El comportamiento SHOULD reutilizar un mismo patrón técnico acordado en la implementación (p. ej. control activable por clic o foco, popover) en ambas pantallas para la columna de ítem, manteniendo la tabla legible y densa.

#### Scenario: Contenido largo en tabla de ítems de la factura

- **WHEN** el usuario visualiza una fila en la que el nombre del ítem aparece truncado en la columna correspondiente
- **THEN** MUST existir una forma explícita de acceder al texto íntegro desde esa celda o desde un control asociado a la misma fila
- **AND** esa forma MUST NOT requerir únicamente el paso del mouse sobre la celda para descubrir el contenido completo

#### Scenario: Contenido largo en tabla de ítems de pagos

- **WHEN** el usuario visualiza una fila de la tabla de ítems de pagos en la que el texto del ítem aparece truncado
- **THEN** MUST existir la misma clase de acceso al texto completo que en la tabla de ítems de la factura para mantener coherencia de producto

#### Scenario: Interacción sin hover fiable

- **WHEN** el usuario interactúa desde un dispositivo o contexto en el que el hover no es un medio principal (p. ej. táctil o navegación por teclado)
- **THEN** MUST poder obtener el texto completo con un camino de interacción al menos equivalente al del puntero (p. ej. activación por foco o toque sobre el control previsto)
