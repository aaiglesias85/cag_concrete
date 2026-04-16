-- --------------------------------------------------------
-- code y contract_name pasan de `item` a nivel de línea de proyecto (`project_item`)
-- y de presupuesto (`estimate_quote_items`). Pueden repetirse en el mismo proyecto y entre proyectos.
-- 1) Añadir columnas nuevas
-- 2) Copiar datos existentes desde item (por item_id de la línea)
-- 3) Eliminar columnas en item
-- --------------------------------------------------------

-- Líneas de proyecto
ALTER TABLE `project_item`
    ADD COLUMN `code` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Código por línea de proyecto',
    ADD COLUMN `contract_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nombre en contrato por línea de proyecto';

UPDATE `project_item` `pi`
INNER JOIN `item` `i` ON `pi`.`item_id` = `i`.`item_id`
SET
    `pi`.`code` = `i`.`code`,
    `pi`.`contract_name` = `i`.`contract_name`
WHERE `i`.`item_id` IS NOT NULL;

-- Líneas de estimate (no hay project_item)
ALTER TABLE `estimate_quote_items`
    ADD COLUMN `code` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Código por línea de estimate',
    ADD COLUMN `contract_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nombre en contrato por línea de estimate';

UPDATE `estimate_quote_items` `eqi`
INNER JOIN `item` `i` ON `eqi`.`item_id` = `i`.`item_id`
SET
    `eqi`.`code` = `i`.`code`,
    `eqi`.`contract_name` = `i`.`contract_name`
WHERE `i`.`item_id` IS NOT NULL;

-- Quitar del catálogo item
ALTER TABLE `item`
    DROP COLUMN `code`,
    DROP COLUMN `contract_name`;
