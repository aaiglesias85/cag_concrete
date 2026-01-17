-- Crear tabla para manejar múltiples combinaciones de clase y precio por proyecto
CREATE TABLE `project_concrete_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `concrete_class_id` int(11) NOT NULL,
  `concrete_quote_price` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `concrete_class_id` (`concrete_class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar foreign keys para project_concrete_class
ALTER TABLE `project_concrete_class` 
  ADD CONSTRAINT `Refprojectconcreteclassprojectid` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `Refprojectconcreteclassclassid` FOREIGN KEY (`concrete_class_id`) REFERENCES `concrete_class` (`concrete_class_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Migrar datos existentes de project a project_concrete_class
INSERT INTO `project_concrete_class` (`project_id`, `concrete_class_id`, `concrete_quote_price`)
SELECT `project_id`, `concrete_class_id`, `concrete_quote_price`
FROM `project`
WHERE `concrete_class_id` IS NOT NULL;
