ALTER TABLE `item` ADD `name` VARCHAR(255) NULL AFTER `item_id`;
UPDATE `item` SET `name` = `description`;