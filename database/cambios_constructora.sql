ALTER TABLE `invoice` ADD `paid` BOOLEAN NULL AFTER `notes`;
UPDATE `invoice` SET `paid` = '0';

ALTER TABLE `invoice_item` ADD `paid_qty` DECIMAL(18,6) NULL AFTER `price`,
    ADD `paid_amount` DECIMAL(18,6) NULL AFTER `paid_qty`,
    ADD `paid_amount_total` DECIMAL(18,6) NULL AFTER `paid_amount`;