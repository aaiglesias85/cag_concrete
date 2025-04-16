ALTER TABLE `project` CHANGE `name` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `project` ADD `name` VARCHAR(255) NULL AFTER `proposal_number`;