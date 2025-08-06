CREATE TABLE estimate_company
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    estimate_id INT(11),
    company_id INT(11),
    contact_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `estimate_company` ADD CONSTRAINT `Refestimate_company1` FOREIGN KEY (`estimate_id`)
    REFERENCES `estimate`(`estimate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_company` ADD CONSTRAINT `Refestimate_company2` FOREIGN KEY (`company_id`)
    REFERENCES `company`(`company_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_company` ADD CONSTRAINT `Refestimate_company3` FOREIGN KEY (`contact_id`)
    REFERENCES `company_contact`(`contact_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;