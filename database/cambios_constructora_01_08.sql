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


CREATE TABLE county
(
    county_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;



INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('32', 'county', 'County');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '32');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '32');

ALTER TABLE `project` ADD `county_id` INT(11) NULL AFTER `inspector_id`, ADD INDEX (`county_id`);

ALTER TABLE `project` ADD CONSTRAINT `Refprojectcountyid` FOREIGN KEY (`county_id`) REFERENCES
    `county`(`county_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `district` ADD `county_id` INT(11) NULL AFTER `status`, ADD INDEX (`county_id`);

ALTER TABLE `district` ADD CONSTRAINT `Refdistrictcountyid` FOREIGN KEY (`county_id`) REFERENCES
    `county`(`county_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate` ADD `county_id` INT(11) NULL AFTER `district_id`, ADD INDEX (`county_id`);

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimatecountyid` FOREIGN KEY (`county_id`) REFERENCES
    `county`(`county_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
