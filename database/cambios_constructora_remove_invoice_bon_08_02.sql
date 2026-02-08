-- Eliminar columnas Bon del invoice.
-- X (Bond Qty) e Y (Bond Amount General) se calculan en frontend y no se guardan en invoice.

ALTER TABLE `invoice`
  DROP COLUMN `bon_quantity_requested`,
  DROP COLUMN `bon_quantity`,
  DROP COLUMN `bon_amount`;
