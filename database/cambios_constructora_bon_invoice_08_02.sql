-- Bon en Invoices: Bond Quantity solicitado (0-1), aplicado y Bond Amount por invoice.
-- Regla: la suma de Bond Quantity aplicado en todos los invoices del proyecto no puede exceder 1.
-- Bond General = monto del ítem Bond del proyecto (quantity * price). Bond Amount = Bond General * Bond Quantity aplicado.

ALTER TABLE `invoice`
  ADD COLUMN `bon_quantity_requested` decimal(10,6) DEFAULT NULL COMMENT 'Bond Quantity solicitado (0 a 1)' AFTER `edit_sequence`,
  ADD COLUMN `bon_quantity` decimal(10,6) DEFAULT NULL COMMENT 'Bond Quantity aplicado (cap por acumulado)' AFTER `bon_quantity_requested`,
  ADD COLUMN `bon_amount` decimal(18,2) DEFAULT NULL COMMENT 'Bond General * Bond Quantity aplicado' AFTER `bon_quantity`;
