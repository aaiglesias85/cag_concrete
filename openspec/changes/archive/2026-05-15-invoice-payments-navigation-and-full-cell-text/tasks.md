## 1. Texto completo en celdas truncadas (compartido)

- [x] 1.1 En `datatable-util.js`, añadir helper de render para columnas de texto largo: celda truncada con estilo existente + mecanismo accesible (p. ej. popover Bootstrap/Metronic) con texto escapado, cierre al abrir otro, init en `drawCallback` si aplica.
- [x] 1.2 Aplicar el helper a la(s) columna(s) de ítem/concepto en `invoices.js`.
- [x] 1.3 Aplicar el mismo patrón a la(s) columna(s) equivalente(s) en `payments.js`.
- [x] 1.4 Probar en escritorio y en viewport táctil o sin hover: clic/foco muestra texto completo; sin regresiones en el listado.

## 2. Cierre

- [x] 2.1 Integrar el delta ADDED de esta carpeta en `openspec/specs/invoicing-payments/spec.md` al archivar el change, sin solapar bloques de requisitos existentes.
- [x] 2.2 Documentación interna del helper solo si el equipo lo pide.
