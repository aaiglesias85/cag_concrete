## Why

En el listado admin de **Payments**, la columna llamada «Amount» no comunica bien que corresponde al **total facturado del invoice** (Final Amount This Period agregado). Los usuarios necesitan ver en paralelo **cuánto se ha pagado** en ese invoice para interpretar saldo de un vistazo.

## What Changes

- Renombrar el encabezado de columna **Amount** → **Invoice** y mantener el valor como **monto total del invoice** (equivalente a la suma de Final Amount This Period por ítems del invoice ya expuesta como `total` en el listado).
- Añadir inmediatamente después una columna **Payment Amount** con el **total pagado acumulado en los ítems del invoice** (suma coherente con `paid_amount` en líneas del invoice/vista de payments).
- Ajustar el payload del endpoint de listado si hace falta un campo nuevo (p. ej. suma pagada por invoice), el DTO/DataTables ordenación si corresponde, y la tabla DataTables (`thead`, columnas, exportación, ordenación por índices).

## Capabilities

### New Capabilities

Ninguno: el comportamiento es extensión del módulo de facturación y pagos ya cubierto en `invoicing-payments`.

### Modified Capabilities

- `invoicing-payments`: añadir requisitos de presentación/datos para el DataTable del listado de payments (columnas Invoice y Payment Amount, significado y formato monetario consistente).

## Impact

- `src/Service/Admin/PaymentService.php` (armado del array por fila en `ListarInvoices`).
- `src/Dto/Admin/Payment/PaymentListarRequest.php` (`allowedOrderFields` si la nueva columna es ordenable).
- `templates/admin/payment/index.html.twig` (encabezados `<th>`).
- `public/assets/metronic8/js/pages/payments.js` (definición de columnas, `columnDefs`, `order` si usa índice de columna, `fixedColumns` si indices cambian).
