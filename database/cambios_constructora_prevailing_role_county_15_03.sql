-- Prevailing Wage: county en project_prevailing_role. Fecha: 2025-03-15

-- Paso 1: Agregar county_id a project_prevailing_role
ALTER TABLE `project_prevailing_role`
ADD COLUMN `county_id` INT NULL AFTER `project_id`,
ADD CONSTRAINT `fk_project_prevailing_role_county`
  FOREIGN KEY (`county_id`) REFERENCES `county` (`county_id`) ON DELETE CASCADE;

-- Paso 2: Migrar: asignar county_id al primer registro por proyecto desde project.prevailing_county_id
UPDATE `project_prevailing_role` ppr
INNER JOIN `project` p ON p.`project_id` = ppr.`project_id`
INNER JOIN (
   SELECT `project_id`, MIN(`id`) AS first_id
   FROM `project_prevailing_role`
   GROUP BY `project_id`
) first_row ON first_row.`project_id` = ppr.`project_id` AND first_row.`first_id` = ppr.`id`
SET ppr.`county_id` = p.`prevailing_county_id`
WHERE p.`prevailing_county_id` IS NOT NULL;

-- Paso 3: Eliminar FK de project.prevailing_county_id y luego la columna
DELIMITER //
DROP PROCEDURE IF EXISTS drop_prevailing_county_fk//
CREATE PROCEDURE drop_prevailing_county_fk()
BEGIN
   DECLARE fk_name VARCHAR(255) DEFAULT NULL;

   SELECT CONSTRAINT_NAME INTO fk_name
   FROM information_schema.KEY_COLUMN_USAGE
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'project'
     AND COLUMN_NAME = 'prevailing_county_id'
     AND REFERENCED_TABLE_NAME IS NOT NULL
   LIMIT 1;

   IF fk_name IS NOT NULL THEN
      SET @drop_fk = CONCAT('ALTER TABLE `project` DROP FOREIGN KEY `', fk_name, '`');
      PREPARE stmt FROM @drop_fk;
      EXECUTE stmt;
      DEALLOCATE PREPARE stmt;
   END IF;
END//
DELIMITER ;

CALL drop_prevailing_county_fk();
DROP PROCEDURE IF EXISTS drop_prevailing_county_fk;

ALTER TABLE `project` DROP COLUMN `prevailing_county_id`;
