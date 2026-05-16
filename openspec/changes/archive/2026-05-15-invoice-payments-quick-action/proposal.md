## Why

Desde el listado de facturas (admin) suele hacer falta registrar o revisar pagos del invoice sin buscarlo de nuevo en la pantalla de Payments. Hoy el flujo exige ir a `/payments`, localizar la fila y abrir el formulario; un acceso directo desde la fila del invoice reduce fricción y errores de contexto.

## What Changes

- Botón/icono de moneda (símbolo peso `$`) en la columna **Actions** del DataTable de invoices (listado admin principal), junto a exportar, editar y eliminar según permisos.
- Al hacer clic, navegar a la pantalla de Payments (`/payments`) y abrir directamente el formulario de edición de pagos del invoice seleccionado (mismo flujo que “editar pago” desde el listado de payments: carga vía `payment/cargarDatos` con `invoice_id`).
- Respetar permisos: el botón solo para usuarios con permiso de edición de **Payments** (o el criterio ya usado para mostrar “paid/edit” en payments); si el invoice está en estado solo lectura como en payments pagados, aplicar la misma regla que en la tabla de payments (p. ej. vista en solo lectura si aplica).
- Sin **BREAKING** en APIs existentes; puede añadirse parámetro de URL o estado en cliente para “deep link” sin romper rutas actuales.

## Capabilities

### New Capabilities

- (ninguno; el comportamiento amplía navegación UX sobre la capacidad ya cubierta por invoicing-payments)

### Modified Capabilities

- `invoicing-payments`: el admin SHALL ofrecer acceso directo desde el listado de invoices al formulario de pagos del invoice (navegación + apertura automática del formulario con el `invoice_id` correcto), alineado con permisos y estados de pago ya definidos para la pantalla de Payments.

## Impact

- Frontend: `public/assets/metronic8/js/pages/invoices.js` (columna acciones, posiblemente `DatatableUtil` si se reutiliza patrón de iconos).
- Frontend: `public/assets/metronic8/js/pages/payments.js` (leer query/hash al cargar y llamar `editRow(invoice_id)` o equivalente).
- Plantilla opcional: `templates/admin/payment/index.html.twig` si hace falta pasar `invoice_id` inicial desde servidor.
- Documentación de rutas ya existente: `payment.yaml` (`/payments`); sin cambio obligatorio de contrato JSON si el deep link es solo URL + JS.
