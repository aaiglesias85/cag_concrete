## Context

El listado de payments admin usa DataTables contra `payment/listar`, construido en `PaymentService::ListarInvoices()`. Por fila el backend ya envía `'total'` como `InvoiceItemRepository::TotalInvoiceFinalAmountThisPeriod()` (total facturado del invoice). La vista Twig etiqueta esa columna como «Amount».

## Goals / Non-Goals

**Goals:**

- Clarificar que la primera columna monetaria es el **monto del invoice** (nombre «Invoice»).
- Exponer una segunda columna **Payment Amount** con la **suma de lo pagado** en los ítems del mismo invoice (misma noción que en la vista de edición/detalle del payment: líneas `paid_amount`).
- Mantener formato monetario consistente con el front existente (`MyApp.formatMoney`).

**Non-Goals:**

- Cambiar las reglas de negocio de override payment, retainage o estados Paid del invoice más allá de lo necesario para leer/importar datos del listado.
- Rediseñar el resto de columnas ni el flujo de filtros.

## Decisions

1. **Valor de Payment Amount**: calcular por invoice como `InvoiceItemRepository::TotalInvoicePaidAmount($invoiceId)` ya existente (suma de `paidAmount` por ítems del invoice filtrados por ese id). coincide con métricas documentadas («Paid Amount» por línea) y evita duplicar SQL ad hoc.

2. **Clave JSON**: añadir al array de cada fila un campo dedicado — p. ej. `paymentAmount` (float ya listo para formatear en cliente, como `total`).

3. **Ordenamiento server-side**: extender `allowedOrderFields` en `PaymentListarRequest` con el nuevo campo **solo si** DataTables permite ordenar por esa columna. Si el repositorio de listados no ordena por suma pagada de forma eficiente, **primera iteración**: columna solo visual (no ordenable) o repetir consulta agregada en el mismo criterio de orden ya usado (`total`). La implementación debe comprobar si `DataTablesHelper` mapea nombres a campos de query; si añadir sort por `paymentAmount` fuerza trabajo grande en repository, registrar en código como siguiente paso (**Open Question** cerrado por implementación práctica**: preferir no ordenable en v1 si el coste es alto).

4. **Índices de columnas**: al insertar una columna nueva tras Invoice, revisar `order: [[3, 'desc']]` (actualmente fecha/proyecto), `columnDefs.targets`, `fixedColumns.left/right`, selectores `:last-child`, y cualquier `[targets: 9]` de Status para desplazar +1 después de la nueva columna.

## Risks / Trade-offs

- **Índices erróneos tras insertar columna** → QA visual + verificar toggle Status y botones Actions; ejecutar ordenación y scroll horizontal.
- **Exportación CSV/Excel** incluye nueva columna de forma esperada mediante columnas Dinámicas de DataTables; validar exclusiones cuando `permiso.eliminar`.

## Migration Plan

Despliegue estándar: sin migración de datos. Rollback revertir JS/Twig/backend.

## Open Questions

- Si los stakeholders requieren **ordenar por Payment Amount** en la primera versión y el backend actual no puede sin ampliar el query principal del listado, acotar alcance antes de cerrar `/opsx:apply`.
