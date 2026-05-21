## Why

En las tablas de **líneas de ítem** de Invoices y Payments, los nombres largos siguen mostrándose truncados en pantalla. En escritorio el usuario suele descubrir el texto completo al pasar el mouse; en **tablet y dispositivos táctiles** ese camino no es fiable y el popover por clic implementado previamente no ofrece una experiencia clara (área de toque pequeña, sin indicación visual, posibles conflictos con el scroll o re-draw del DataTable). Hace falta reforzar el acceso al texto íntegro en contexto táctil sin sacrificar la densidad de la grilla.

## What Changes

- Mejorar el patrón compartido en `datatable-util.js` para texto truncado: interacción **prioritaria por toque/clic**, cierre al tocar fuera, y affordance visible cuando el texto está recortado.
- Aplicar el patrón mejorado en la columna **Item** de `#items-table-editable` (invoices) y `#payments-table-editable` (payments).
- Opcionalmente extender el mismo control a la columna **Code** cuando el código de ítem también se trunca y el usuario lo necesita leer completo.
- Estilos mínimos (CSS) para popover de texto largo: ancho máximo, scroll interno y legibilidad en viewport reducido.
- Verificación manual en viewport táctil (~768px) y escritorio.

## Capabilities

### New Capabilities

- _(ninguna: se amplía el comportamiento ya definido en facturación y pagos)_

### Modified Capabilities

- `invoicing-payments`: requisitos de visibilidad del texto completo en celdas truncadas — exigir interacción táctil fiable y affordance explícita en la columna de ítem (delta en `specs/invoicing-payments/spec.md`).

## Impact

- Front: `public/assets/metronic8/js/utils/datatable-util.js`, `public/assets/metronic8/js/pages/invoices.js`, `public/assets/metronic8/js/pages/payments.js`, y CSS compartido Metronic/admin si hace falta para `.dt-longtext-popover`.
- Back: ninguno.
- Spec canónica: delta frente a `openspec/specs/invoicing-payments/spec.md` (requisito existente desde el change archivado `invoice-payments-navigation-and-full-cell-text`).
