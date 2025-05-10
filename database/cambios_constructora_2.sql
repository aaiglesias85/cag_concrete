INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('22', 'schedule', 'Schedule Document');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '22');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '22');

CREATE TABLE schedule
(
    schedule_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    location VARCHAR(255),
    latitud VARCHAR(50),
    longitud VARCHAR(50),
    day DATETIME,
    quantity DECIMAL(18,6),
    notes TEXT,
    project_id INT(11),
    project_contact_id INT(11),
    vendor_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `schedule` ADD CONSTRAINT `Refscheduleprojectid` FOREIGN KEY (`project_id`)
    REFERENCES `project`(`project_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `schedule` ADD CONSTRAINT `Refscheduleprojectcontactid` FOREIGN KEY (`project_contact_id`)
    REFERENCES `project_contact`(`contact_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `schedule` ADD CONSTRAINT `Refscheduleconcvendorid` FOREIGN KEY (`vendor_id`)
    REFERENCES `concrete_vendor`(`vendor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

CREATE TABLE schedule_concrete_vendor_contact
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT(11),
    contact_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `schedule_concrete_vendor_contact` ADD CONSTRAINT `Refs_chedule_concrete_vendor_contacts_cheduleid` FOREIGN KEY (`schedule_id`)
    REFERENCES `schedule`(`schedule_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `schedule_concrete_vendor_contact` ADD CONSTRAINT `Refs_chedule_concrete_vendor_contacts_contactid` FOREIGN KEY (`contact_id`)
    REFERENCES `concrete_vendor_contact`(`contact_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
