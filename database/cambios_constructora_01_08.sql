CREATE TABLE schedule_employee
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT(11),
    employee_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `schedule_employee` ADD CONSTRAINT `Refschedule_employee1` FOREIGN KEY (`schedule_id`)
    REFERENCES `schedule`(`schedule_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `schedule_employee` ADD CONSTRAINT `Refschedule_employee2` FOREIGN KEY (`employee_id`)
    REFERENCES `employee`(`employee_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

