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