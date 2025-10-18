CREATE TABLE invoice_attachment
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    file VARCHAR(255),
    invoice_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `invoice_attachment` ADD CONSTRAINT `Refinvoice_attachment1` FOREIGN KEY (`invoice_id`)
    REFERENCES `invoice`(`invoice_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;



CREATE TABLE invoice_notes
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    notes TEXT,
    date DATE,
    invoice_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `invoice_notes` ADD CONSTRAINT `Refinvoice_notes1` FOREIGN KEY (`invoice_id`)
    REFERENCES `invoice`(`invoice_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
