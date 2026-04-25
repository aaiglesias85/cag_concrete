CREATE TABLE estimate_attachment
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    file VARCHAR(255),
    estimate_id INT(11) NOT NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `estimate_attachment` ADD CONSTRAINT `Refestimate_attachment1` FOREIGN KEY (`estimate_id`)
    REFERENCES `estimate`(`estimate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
