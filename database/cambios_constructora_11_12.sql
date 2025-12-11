-- Crear tabla intermedia para relación many-to-many entre project y county
CREATE TABLE `project_county` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `county_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `county_id` (`county_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar foreign keys para project_county
ALTER TABLE `project_county` 
  ADD CONSTRAINT `Refprojectcountyprojectid` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `Refprojectcountycountyid` FOREIGN KEY (`county_id`) REFERENCES `county` (`county_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

-- Migrar datos existentes de project.county_id a project_county
INSERT INTO `project_county` (`project_id`, `county_id`)
SELECT `project_id`, `county_id` 
FROM `project` 
WHERE `county_id` IS NOT NULL;

-- Agregar índice único compuesto para evitar duplicados en project_county
ALTER TABLE `project_county` 
  ADD UNIQUE KEY `unique_project_county` (`project_id`, `county_id`);
