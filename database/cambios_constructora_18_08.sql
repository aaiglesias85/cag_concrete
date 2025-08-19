ALTER TABLE `project_item` ADD `principal` BOOLEAN NULL AFTER `price_old`;
UPDATE `project_item` SET `principal` = '1';