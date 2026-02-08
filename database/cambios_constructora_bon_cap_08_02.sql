-- Bon con regla de tope: Bond Quantity aplicado por invoice, suma total ≤ 1.
-- bon_quantity = Bond Quantity (X) aplicado (cap por acumulado).
-- bon_amount = Bond General × bon_quantity.

-- Si las columnas ya existen (p. ej. por bon_invoice_08_02), no ejecutar o comentar.
ALTER TABLE `invoice`
  ADD COLUMN `bon_quantity` decimal(10,6) DEFAULT NULL COMMENT 'Bond Quantity aplicado (0-1, cap por proyecto)' AFTER `edit_sequence`,
  ADD COLUMN `bon_amount` decimal(18,2) DEFAULT NULL COMMENT 'Bond General × bon_quantity' AFTER `bon_quantity`;
