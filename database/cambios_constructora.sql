INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('18', 'subcontractor', 'Subcontractor');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '18');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '18');

CREATE TABLE subcontractor
(
    subcontractor_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    address TEXT,
    phone VARCHAR(50),
    contact_name VARCHAR(255),
    contact_email VARCHAR(255)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE subcontractor_employee
(
    subcontractor_employee_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    hourly_rate DECIMAL(8,2),
    position VARCHAR(255),
    subcontractor_id INT
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE INDEX `Ref63452` ON subcontractor_employee (subcontractor_id);

ALTER TABLE subcontractor_employee
    ADD CONSTRAINT `Refsubcontractor35` FOREIGN KEY (subcontractor_id) REFERENCES subcontractor (subcontractor_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

CREATE TABLE subcontractor_notes
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    notes TEXT,
    date DATE,
    subcontractor_id INT
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE INDEX `Ref63453` ON subcontractor_notes (subcontractor_id);

ALTER TABLE subcontractor_notes
    ADD CONSTRAINT `Refsubcontractor36` FOREIGN KEY (subcontractor_id) REFERENCES subcontractor (subcontractor_id) ON DELETE NO ACTION ON UPDATE NO ACTION;


ALTER TABLE `subcontractor` ADD `created_at` DATETIME NULL AFTER `contact_email`, ADD `updated_at` DATETIME NULL AFTER `created_at`;

ALTER TABLE `data_tracking_labor` ADD `subcontractor_employee_id` INT(11) NULL AFTER `employee_id`, ADD INDEX (`subcontractor_employee_id`);
ALTER TABLE `data_tracking_labor` ADD CONSTRAINT `fk_data_tracking_labor_subcontractor_employee` FOREIGN KEY (`subcontractor_employee_id`) REFERENCES `subcontractor_employee`(`subcontractor_employee_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `data_tracking_labor` CHANGE `employee_id` `employee_id` INT(11) NULL;

ALTER TABLE `data_tracking_subcontract` ADD `project_item_id` INT(11) NULL AFTER `item_id`, ADD INDEX (`project_item_id`);
ALTER TABLE `data_tracking_subcontract` ADD CONSTRAINT `Refdatatrackingsubcontract37` FOREIGN KEY (`project_item_id`) REFERENCES `project_item`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `data_tracking_subcontract` ADD `subcontractor_id` INT(11) NULL AFTER `data_tracking_id`, ADD INDEX (`subcontractor_id`);
ALTER TABLE `data_tracking_subcontract` ADD CONSTRAINT `Refdatatrackingsubcontract38` FOREIGN KEY (`subcontractor_id`) REFERENCES `subcontractor`(`subcontractor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('19', 'reporte_subcontractor', 'Subcontractors');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '19');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '19');


ALTER TABLE `subcontractor`
    ADD `company_name` VARCHAR(255) NULL AFTER `contact_email`,
    ADD `company_phone` VARCHAR(50) NULL AFTER `company_name`,
    ADD `company_address` VARCHAR(255) NULL AFTER `company_phone`;