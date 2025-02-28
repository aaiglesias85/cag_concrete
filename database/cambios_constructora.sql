CREATE TABLE advertisement
(
    advertisement_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title   VARCHAR(255),
    description      TEXT,
    status tinyint(1),
    start_date DATE,
    end_date DATE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('17', 'advertisement', 'Advertisements');

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '17');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '17');