-- Bon en Invoices: Bon General del proyecto y Bon Quantity/Amount por invoice
-- Ver README_BONED_CALCULATION.md (sección Cálculo de Bon)

-- 1. Bon General del proyecto (valor total del Bon asignado al proyecto)
ALTER TABLE `project`
  ADD COLUMN `bon_general` decimal(18,2) DEFAULT NULL COMMENT 'Bon total del proyecto (ej: -1850)' AFTER `prevailing_rate`;

-- 2. Por invoice: solicitado, aplicado y monto
ALTER TABLE `invoice`
  ADD COLUMN `bon_quantity_requested` decimal(10,6) DEFAULT NULL COMMENT 'Bon Quantity solicitado (0 a 1)' AFTER `edit_sequence`,
  ADD COLUMN `bon_quantity` decimal(10,6) DEFAULT NULL COMMENT 'Bon Quantity aplicado (cap por acumulado)' AFTER `bon_quantity_requested`,
  ADD COLUMN `bon_amount` decimal(18,2) DEFAULT NULL COMMENT 'Bon General * Bon Quantity aplicado' AFTER `bon_quantity`;
