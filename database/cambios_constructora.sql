INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('21', 'conc_vendor', 'Concrete Vendors');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '21');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '21');

CREATE TABLE concrete_vendor
(
    vendor_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    address TEXT,
    phone VARCHAR(50),
    contact_name VARCHAR(255),
    contact_email VARCHAR(255)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `data_tracking_conc_vendor` ADD `vendor_id` INT(11) NULL AFTER `data_tracking_id`, ADD INDEX (`vendor_id`);
ALTER TABLE `data_tracking_conc_vendor` ADD CONSTRAINT `Refdatatrackingconcvendor36` FOREIGN KEY (`vendor_id`)
    REFERENCES `concrete_vendor`(`vendor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;