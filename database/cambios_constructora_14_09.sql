ALTER TABLE `project` ADD `vendor_id` INT(11) NULL AFTER `county_id`, ADD INDEX (`vendor_id`);

ALTER TABLE `project` ADD CONSTRAINT `Refconcretevendorid` FOREIGN KEY (`vendor_id`) REFERENCES
    `concrete_vendor`(`vendor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `project` ADD `concrete_quote_price` DECIMAL(18,2) NULL AFTER `po_cg`;

ALTER TABLE `project` ADD `concrete_quote_price_escalator` DECIMAL(18,2) NULL AFTER `concrete_quote_price`;

ALTER TABLE `project` ADD `concrete_time_period_every_n` INT(11) NULL AFTER `concrete_quote_price_escalator`,
    ADD `concrete_time_period_unit` ENUM('day','month','year','') NULL AFTER `concrete_time_period_every_n`;