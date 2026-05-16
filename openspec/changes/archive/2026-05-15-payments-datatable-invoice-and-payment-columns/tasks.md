## 1. Backend (listado `payment/listar`)

- [x] 1.1 En `PaymentService::ListarInvoices()` (construcción del array `$data`), añadir por invoice el campo `paymentAmount` (o nombre acordado) usando `InvoiceItemRepository::TotalInvoicePaidAmount()` con el `invoice_id` de la fila, en paralelo al cálculo existente de `$total`.
- [x] 1.2 Revisar `PaymentListarRequest::fromHttpRequest` y `DataTablesHelper::parse`: incluir `paymentAmount` en `allowedOrderFields` solo si implementas orden reproducible desde el servidor; si no, dejar la columna no ordenable en DataTables sin tocar campos prohibidos del parse.

## 2. Plantilla Twig

- [x] 2.1 En `templates/admin/payment/index.html.twig`, sustituir el `<th>` de **Amount** por **Invoice** e insertar a continuación un `<th>Payment Amount</th>` antes de Notes.

## 3. Frontend DataTables

- [x] 3.1 En `public/assets/metronic8/js/pages/payments.js`, insertar la nueva columna de datos después de `{ data: 'total' }` con `{ data: 'paymentAmount' }` y formatear con `MyApp.formatMoney` igual que la columna de invoice total.
- [x] 3.2 Actualizar todos los `columnDefs` cuyos `targets` sean índices numéricos a partir del punto de inserción (Notes, Created At, Status, etc.) para que coincidan con el orden real de columnas tras el insert (incl. `targets: -1` para Actions si sigue siendo válido).
- [x] 3.3 Revalidar configuración sensible a índices: `order: [[..., 'desc']]`, `fixedColumns.start/end`, y exportación/copy de columnas cuando `permiso.eliminar` cambia inclusión/exclusión del primer campo.
- [x] 3.4 Marcar `paymentAmount` como `orderable: false` si el backend no ordena por ese campo, evitando peticiones rechazadas o orden incorrecto.

## 4. Verificación

- [x] 4.1 Probar pantalla Payments con filtros cargados: valores de **Invoice** y **Payment Amount** coherentes con el detalle al abrir ese invoice para edición/visualización de líneas.
- [x] 4.2 Probar ordenación donde aplique y export CSV/Excel/PDF desde el menú de exportación si las columnas quedaron alineadas con la cabecera.

## 5. OpenSpec apply (fusión final)

- [x] 5.1 Ejecutar `/opsx:apply` para implementar código y aplicar delta de `openspec/changes/payments-datatable-invoice-and-payment-columns/specs/invoicing-payments/spec.md` al spec canónico bajo control del flujo openspec (`openspec/specs/invoicing-payments/spec.md`).
