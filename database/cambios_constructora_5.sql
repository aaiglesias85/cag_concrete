ALTER TABLE `schedule` CHANGE `day` `day` DATE NULL DEFAULT NULL;
ALTER TABLE `schedule` ADD `hour` VARCHAR(50) NULL AFTER `day`;
ALTER TABLE `schedule` ADD `highpriority` BOOLEAN NULL AFTER `notes`;