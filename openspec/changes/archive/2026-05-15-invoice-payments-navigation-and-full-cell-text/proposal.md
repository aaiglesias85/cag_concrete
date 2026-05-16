## Why

En las tablas DataTable de **líneas de ítem** al editar una factura o pagos, los nombres largos se recortan (ellipsis) y confiar solo en `title`/hover no cubre bien táctil, teclado ni contextos sin puntero. Hace falta un modo explícito de leer el texto completo sin perder densidad en la grilla.

## What Changes

- En la vista de factura (`#items-table-editable`) y en la vista de pagos (`#payments-table-editable`), patrón de UI en la columna **ítem**: ver el texto **íntegro** sin depender solo del hover (popover Bootstrap activable por clic/foco).
- `datatable-util.js`: helpers reutilizables (`buildTruncatedTextPopoverTrigger`, `initTruncatedTextPopovers`) y cierre del popover anterior al abrir otro.

## Capabilities

### New Capabilities

- _(ninguno: el comportamiento amplía la capability existente de facturación y pagos)_

### Modified Capabilities

- `invoicing-payments`: visibilidad del texto completo en la columna de ítem cuando está truncada en las tablas de líneas de factura y de pagos (delta en `specs/invoicing-payments/spec.md`; integrado también en la spec canónica).

## Impact

- Front: `public/assets/metronic8/js/pages/invoices.js`, `public/assets/metronic8/js/pages/payments.js`, `public/assets/metronic8/js/utils/datatable-util.js` (u renderers compartidos), plantillas Metronic si el markup lo requiere.
- Back: ninguno previsto salvo que el listado deba exponer campos nuevos para render (no esperado).
- Especificación: delta en esta carpeta bajo `specs/invoicing-payments/spec.md` frente a `openspec/specs/invoicing-payments/spec.md`.
