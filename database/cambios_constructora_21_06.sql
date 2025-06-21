CREATE TABLE estimate_quote
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    quantity DECIMAL(18,6),
    price DECIMAL(18,6),
    yield_calculation VARCHAR(50),
    estimate_id INT(11),
    item_id INT(11),
    equation_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `estimate_quote` ADD CONSTRAINT `Refestimate_quote1` FOREIGN KEY (`estimate_id`)
    REFERENCES `estimate`(`estimate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_quote` ADD CONSTRAINT `Refestimate_quote2` FOREIGN KEY (`item_id`)
    REFERENCES `item`(`item_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `estimate_quote` ADD CONSTRAINT `Refestimate_quote3` FOREIGN KEY (`equation_id`)
    REFERENCES `equation`(`equation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
