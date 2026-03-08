-- Agregar columna rate a project_prevailing_role (rate por labor type)
-- La tabla ya existe; solo se agrega el campo y se migran datos desde project.prevailing_rate

ALTER TABLE `project_prevailing_role`
ADD COLUMN `rate` DECIMAL(18,2) NULL AFTER `role_id`;

-- Migrar rate existente desde project hacia cada fila de project_prevailing_role
UPDATE `project_prevailing_role` ppr
INNER JOIN `project` p ON p.`project_id` = ppr.`project_id`
SET ppr.`rate` = p.`prevailing_rate`
WHERE p.`prevailing_rate` IS NOT NULL;
