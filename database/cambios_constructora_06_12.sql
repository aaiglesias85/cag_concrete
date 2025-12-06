ALTER TABLE `project_price_adjustment` ADD `items_id` TEXT NULL AFTER `percent`;



--- concrete class
INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('36', 'concrete_class', 'Concrete Class');

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '36');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '36');

-- create table
CREATE TABLE `concrete_class` (
   `concrete_class_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `name` VARCHAR(255) NOT NULL,
   `status` BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- agregar concrete_class_id a project como llave foranea
ALTER TABLE `project` ADD `concrete_class_id` INT NULL AFTER `vendor_id`, ADD INDEX (`concrete_class_id`);

ALTER TABLE `project` ADD CONSTRAINT `Refconcreteclassid` FOREIGN KEY (`concrete_class_id`) REFERENCES
    `concrete_class`(`concrete_class_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

