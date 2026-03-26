-- employee role
INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('37', 'employee_role', 'Employee Role');

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '37');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '37');