ALTER TABLE `project_item` ADD `change_order` BOOLEAN NULL AFTER `principal`,
 ADD `change_order_date` DATETIME NULL AFTER `change_order`;