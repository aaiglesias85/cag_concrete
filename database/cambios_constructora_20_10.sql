CREATE TABLE invoice_item_notes
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    notes TEXT,
    date DATE,
    invoice_item_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `invoice_item_notes` ADD CONSTRAINT `Refinvoice_item_notes1` FOREIGN KEY (`invoice_item_id`)
    REFERENCES `invoice_item`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;