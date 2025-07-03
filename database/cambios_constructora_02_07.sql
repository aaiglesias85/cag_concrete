INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('31', 'holiday', 'Holidays');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '31');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '31');

CREATE TABLE holiday
(
    holiday_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    day DATE,
    description VARCHAR(255)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;