INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('29', 'estimate', 'Estimates');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '29');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '29');

CREATE TABLE estimate
(
    estimate_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(255),
    name VARCHAR(255),
    bid_deadline DATETIME,
    county VARCHAR(255),
    priority VARCHAR(50),
    bid_no VARCHAR(50),
    work_hour VARCHAR(50),
    phone TEXT,
    email TEXT,
    project_stage_id INT(11),
    proposal_type_id INT(11),
    status_id INT(11),
    district_id INT(11),
    company_id INT(11),
    contact_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate1` FOREIGN KEY (`project_stage_id`)
    REFERENCES `project_stage`(`stage_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate2` FOREIGN KEY (`proposal_type_id`)
    REFERENCES `proposal_type`(`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate3` FOREIGN KEY (`status_id`)
    REFERENCES `plan_status`(`status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate4` FOREIGN KEY (`district_id`)
    REFERENCES `district`(`district_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate5` FOREIGN KEY (`company_id`)
    REFERENCES `company`(`company_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate6` FOREIGN KEY (`contact_id`)
    REFERENCES `company_contact`(`contact_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


CREATE TABLE estimate_project_type
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    estimate_id INT(11),
    type_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `estimate_project_type` ADD CONSTRAINT `Refestimate_project_type1` FOREIGN KEY (`estimate_id`)
    REFERENCES `estimate`(`estimate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_project_type` ADD CONSTRAINT `Refestimate_project_type2` FOREIGN KEY (`type_id`)
    REFERENCES `project_type`(`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


CREATE TABLE estimate_estimator
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    estimate_id INT(11),
    user_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `estimate_estimator` ADD CONSTRAINT `Refestimate_estimator1` FOREIGN KEY (`estimate_id`)
    REFERENCES `estimate`(`estimate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_estimator` ADD CONSTRAINT `Refestimate_estimator2` FOREIGN KEY (`user_id`)
    REFERENCES `user`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;