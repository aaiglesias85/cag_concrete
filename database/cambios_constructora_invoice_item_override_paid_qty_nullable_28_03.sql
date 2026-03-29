-- Override Payment: paid_qty opcional en invoice_item_override_payment.
-- NULL = no override de paid (se usa el paid agregado de factura / invoice_item).
-- Alineado con entidad InvoiceItemOverridePayment y salvarNotaOverrideUnpaid (solo unpaid).

ALTER TABLE `invoice_item_override_payment`
  MODIFY `paid_qty` decimal(18,6) DEFAULT NULL COMMENT 'Override paid qty; NULL = no override de paid';
