ALTER TABLE `schedule` ADD `employee_id` INT(11) NULL AFTER `vendor_id`, ADD INDEX (`employee_id`);

ALTER TABLE `schedule` ADD CONSTRAINT `Refscheduleemployeeid` FOREIGN KEY (`employee_id`) REFERENCES
    `employee`(`employee_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;