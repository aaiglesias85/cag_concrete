## 1. Helper compartido (datatable-util)

- [x] 1.1 Ajustar `buildTruncatedTextPopoverTrigger`: markup con área táctil amplia, icono opcional y atributos para detección de truncamiento.
- [x] 1.2 En `initTruncatedTextPopovers`: usar trigger priorizando `click` (y foco para teclado); `stopPropagation` en apertura; marcar elementos con truncamiento real (`scrollWidth > clientWidth`).
- [x] 1.3 Reforzar cierre: popover exclusivo al abrir otro; dismiss al tocar fuera (`click` en document con exclusión del trigger).
- [x] 1.4 Añadir estilos `.dt-longtext-popover` y `.dt-longtext--truncated` (max-width, word-break, scroll interno).

## 2. Pantallas Invoices y Payments

- [x] 2.1 Verificar columna **Item** en `invoices.js` (`#items-table-editable`): usa helper y `drawCallback` llama `initTruncatedTextPopovers`.
- [x] 2.2 Verificar columna **Item** en `payments.js` (`#payments-table-editable`): mismo patrón que invoices.
- [x] 2.3 (Opcional) Aplicar helper en columna **Code** (`targets: 0`) en ambos archivos si el truncamiento también afecta códigos largos.

## 3. Verificación

- [x] 3.1 Probar en escritorio: hover opcional, clic abre/cierra texto completo; sin regresiones en badges e iconos de history.
- [x] 3.2 Probar en viewport táctil (~768px o DevTools touch): un toque en ítem truncado muestra nombre completo; segundo toque fuera cierra.
- [x] 3.3 Confirmar que ítems no truncados no muestran icono «ver más».

## 4. Cierre OpenSpec

- [x] 4.1 Al archivar el change, integrar el delta MODIFIED en `openspec/specs/invoicing-payments/spec.md`.
