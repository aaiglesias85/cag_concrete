ALTER TABLE `project_item` ADD `quantity_old` DECIMAL(18,6) NULL AFTER `yield_calculation`,
    ADD `price_old` DECIMAL(18,6) NULL AFTER `quantity_old`;


CREATE TABLE project_price_adjustment
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    day DATE,
    percent DECIMAL(8,2),
    project_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `project_price_adjustment` ADD CONSTRAINT `Refproject_price_adjustment1` FOREIGN KEY (`project_id`)
    REFERENCES `project`(`project_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;