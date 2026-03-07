-- Crear tabla project_prevailing_role para permitir múltiples Labor Types por proyecto
-- Esta tabla reemplaza el campo prevailing_role_id de la tabla project

CREATE TABLE `project_prevailing_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_role` (`project_id`, `role_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_role_id` (`role_id`),
  CONSTRAINT `fk_project_prevailing_role_project` 
    FOREIGN KEY (`project_id`) 
    REFERENCES `project` (`project_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_project_prevailing_role_role` 
    FOREIGN KEY (`role_id`) 
    REFERENCES `employee_role` (`role_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrar datos existentes de prevailing_role_id a la nueva tabla
INSERT INTO `project_prevailing_role` (`project_id`, `role_id`)
SELECT `project_id`, `prevailing_role_id`
FROM `project`
WHERE `prevailing_role_id` IS NOT NULL;

-- Eliminar la foreign key de prevailing_role_id en project
ALTER TABLE `project` DROP FOREIGN KEY `Refprojectprevailingroleid`;

-- Eliminar el índice de prevailing_role_id
ALTER TABLE `project` DROP INDEX `prevailing_role_id`;

-- Eliminar el campo prevailing_role_id de la tabla project
ALTER TABLE `project` DROP COLUMN `prevailing_role_id`;
