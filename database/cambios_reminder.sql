INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('23', 'reminder', 'Reminders');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '23');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '23');

CREATE TABLE reminder
(
    reminder_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255),
    body TEXT,
    day DATE,
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE reminder_recipient
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reminder_id INT(11),
    user_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `reminder_recipient` ADD CONSTRAINT `Refreminderrecipient1` FOREIGN KEY (`reminder_id`)
    REFERENCES `reminder`(`reminder_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `reminder_recipient` ADD CONSTRAINT `Refreminderrecipient2` FOREIGN KEY (`user_id`)
    REFERENCES `user`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;