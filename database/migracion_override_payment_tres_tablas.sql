-- Consolidación: 4 tablas → 3
-- invoice_item_override_payment: + unpaid_qty (NULL = sin override de unpaid; valor = unpaid sobreescrito)
-- invoice_item_override_payment_history → invoice_item_override_payment_paid_qty_history
-- invoice_item_override_unpaid_qty_history → invoice_item_override_payment_unpaid_qty_history (FK a payment)
-- Eliminar invoice_item_override_unpaid_qty
--
-- Requisito: tablas vacías o migrar datos manualmente antes.
-- Orden importante por FKs.

SET FOREIGN_KEY_CHECKS = 0;

-- 1) Paid qty history: renombrar tabla
RENAME TABLE `invoice_item_override_payment_history` TO `invoice_item_override_payment_paid_qty_history`;

-- 2) Quitar FK del historial de unpaid hacia la tabla padre que vamos a eliminar
ALTER TABLE `invoice_item_override_unpaid_qty_history`
  DROP FOREIGN KEY `fk_invoice_item_override_unpaid_qty_history_parent`;

-- 3) Eliminar tabla padre de unpaid (solo override unpaid separado)
DROP TABLE IF EXISTS `invoice_item_override_unpaid_qty`;

-- 4) Renombrar historial de unpaid y columna FK
RENAME TABLE `invoice_item_override_unpaid_qty_history` TO `invoice_item_override_payment_unpaid_qty_history`;

ALTER TABLE `invoice_item_override_payment_unpaid_qty_history`
  CHANGE COLUMN `invoice_item_override_unpaid_qty_id` `invoice_item_override_payment_id` int(11) NOT NULL COMMENT 'FK a invoice_item_override_payment';

ALTER TABLE `invoice_item_override_payment_unpaid_qty_history`
  ADD CONSTRAINT `fk_inv_ovr_pay_unpaid_hist_payment`
  FOREIGN KEY (`invoice_item_override_payment_id`) REFERENCES `invoice_item_override_payment` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- 5) paid qty history: opcional renombrar índice/ FK si existían nombres viejos (MySQL conserva nombres al RENAME)
-- Ajustar nombre de FK hacia payment para claridad (si existe):
-- ALTER TABLE `invoice_item_override_payment_paid_qty_history` DROP FOREIGN KEY `<nombre>`;
-- ALTER TABLE ... ADD CONSTRAINT ... (puede seguir igual tras RENAME)

-- 6) Columna unpaid_qty en línea única de override
ALTER TABLE `invoice_item_override_payment`
  ADD COLUMN `unpaid_qty` decimal(18,6) DEFAULT NULL COMMENT 'Override unpaid qty; NULL = no aplica, usar derivado (qty - paid)' AFTER `paid_qty`;

SET FOREIGN_KEY_CHECKS = 1;
