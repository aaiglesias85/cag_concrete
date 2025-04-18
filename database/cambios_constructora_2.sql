ALTER TABLE `project` CHANGE `name` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `project` ADD `name` VARCHAR(255) NULL AFTER `proposal_number`;


INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('20', 'reporte_employee', 'Employees');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '20');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '20');