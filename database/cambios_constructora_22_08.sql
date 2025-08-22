CREATE TABLE project_attachment
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    file VARCHAR(255),
    project_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `project_attachment` ADD CONSTRAINT `Refproject_attachment1` FOREIGN KEY (`project_id`)
    REFERENCES `project`(`project_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


CREATE TABLE data_tracking_attachment
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    file VARCHAR(255),
    data_tracking_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;


ALTER TABLE `data_tracking_attachment` ADD CONSTRAINT `Refdata_tracking_attachment1` FOREIGN KEY (`data_tracking_id`)
    REFERENCES `data_tracking`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;