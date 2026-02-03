-- Campo override_unpaid_qty en invoice_item_notes (Override Unpaid Qty al guardar nota del ítem)
ALTER TABLE `invoice_item_notes` ADD `override_unpaid_qty` DECIMAL(18,6) NULL DEFAULT NULL AFTER `invoice_item_id`;
