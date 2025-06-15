CREATE TABLE estimate_bid_deadline
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bid_deadline DATETIME,
    estimate_id INT(11),
    company_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `estimate_bid_deadline` ADD CONSTRAINT `Refestimate_bid_deadline1` FOREIGN KEY (`estimate_id`)
    REFERENCES `estimate`(`estimate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_bid_deadline` ADD CONSTRAINT `Refestimate_bid_deadline2` FOREIGN KEY (`company_id`)
    REFERENCES `company`(`company_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


ALTER TABLE `estimate` ADD `job_walk` DATETIME NULL AFTER `email`;
ALTER TABLE `estimate` ADD `rfi_due_date` DATETIME NULL AFTER `job_walk`,
    ADD `project_start` DATETIME NULL AFTER `rfi_due_date`,
    ADD `project_end` DATETIME NULL AFTER `project_start`,
    ADD `submitted_date` DATETIME NULL AFTER `project_end`,
    ADD `awarded_date` DATETIME NULL AFTER `submitted_date`;

ALTER TABLE `estimate` ADD `lost_date` DATETIME NULL AFTER `awarded_date`,
    ADD `location` TEXT NULL AFTER `lost_date`, ADD `sector` VARCHAR(50) NULL AFTER `location`;


INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('30', 'plan_downloading', 'Plans Downloading');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '30');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '30');

CREATE TABLE plan_downloading
(
    plan_downloading_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

INSERT INTO `plan_downloading` (`plan_downloading_id`, `description`, `status`) VALUES
(1, 'In Progress', 1),
(2, 'Done', 1),
(3, 'Done - Requested Scopes Not Found', 1),
(4, 'No Plans Available', 1),
(5, 'Invalid Platform Credentials', 1),
(6, 'Limit Reached', 1);

ALTER TABLE `estimate` ADD `plan_downloading_id` INT(11) NULL AFTER `contact_id`, ADD INDEX (`plan_downloading_id`);
ALTER TABLE `estimate` ADD CONSTRAINT `Refestimate7` FOREIGN KEY (`plan_downloading_id`) REFERENCES
    `plan_downloading`(`plan_downloading_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;