-- Crear tabla employee_role con los campos description y status
CREATE TABLE `employee_role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `unique_description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1. Poblar la tabla employee_role con los valores únicos del campo position de employee
-- Solo inserta valores no nulos y no vacíos, con status = 1 por defecto
INSERT IGNORE INTO `employee_role` (`description`, `status`)
SELECT DISTINCT `position`, 1
FROM `employee`
WHERE `position` IS NOT NULL 
  AND `position` != ''
  AND TRIM(`position`) != '';

-- 2. Agregar el campo role_id a la tabla employee como llave foránea
ALTER TABLE `employee`
ADD COLUMN `role_id` int(11) DEFAULT NULL AFTER `position`,
ADD KEY `idx_role_id` (`role_id`),
ADD CONSTRAINT `fk_employee_role` 
  FOREIGN KEY (`role_id`) 
  REFERENCES `employee_role` (`role_id`) 
  ON DELETE SET NULL 
  ON UPDATE CASCADE;

-- 3. Actualizar los registros de employee con el role_id correspondiente a su position
UPDATE `employee` e
INNER JOIN `employee_role` er ON e.`position` = er.`description`
SET e.`role_id` = er.`role_id`
WHERE e.`position` IS NOT NULL 
  AND e.`position` != ''
  AND TRIM(e.`position`) != '';


-- employee role
INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('37', 'employee_role', 'Employee Role');

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '37');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '37');