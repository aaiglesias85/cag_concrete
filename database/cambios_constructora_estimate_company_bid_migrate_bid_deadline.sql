-- ============================================================================
-- Migración: datos de estimate_bid_deadline → estimate_company
-- -----------------------------------------------------------------------------
-- 1) Añade a estimate_company los campos equivalentes a estimate_bid_deadline.
-- 2) Copia datos: por cada par (estimate_id, company_id), si ya existe
--    estimate_company se actualiza; si no, se inserta una fila nueva
--    (contact_id NULL) para no perder plazos huérfanos.
-- 3) Si había varios deadlines para el mismo par, se usa la fila de
--    estimate_bid_deadline con MAX(id) (la última insertada).
-- 4) Elimina la tabla estimate_bid_deadline y sus FKs.
--
-- Ejecutar una vez contra la BD activa (backup previo recomendado).
-- ============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1) Nuevas columnas en estimate_company (paridad con estimate_bid_deadline)
-- -----------------------------------------------------------------------------
ALTER TABLE `estimate_company`
  ADD COLUMN `bid_deadline` datetime DEFAULT NULL AFTER `contact_id`,
  ADD COLUMN `tag` varchar(50) DEFAULT NULL AFTER `bid_deadline`,
  ADD COLUMN `address` text AFTER `tag`;

-- -----------------------------------------------------------------------------
-- 2a) Actualizar filas estimate_company que ya existen y coinciden con un bid
--     (un deadline por par estimate_id+company_id: MAX(id) en estimate_bid_deadline)
-- -----------------------------------------------------------------------------
UPDATE `estimate_company` ec
INNER JOIN (
  SELECT `estimate_id`, `company_id`, MAX(`id`) AS `max_id`
  FROM `estimate_bid_deadline`
  GROUP BY `estimate_id`, `company_id`
) pick ON pick.`estimate_id` <=> ec.`estimate_id`
  AND pick.`company_id` <=> ec.`company_id`
INNER JOIN `estimate_bid_deadline` ebd ON ebd.`id` = pick.`max_id`
SET
  ec.`bid_deadline` = ebd.`bid_deadline`,
  ec.`tag` = ebd.`tag`,
  ec.`address` = ebd.`address`;

-- -----------------------------------------------------------------------------
-- 2b) Insertar estimate_company para deadlines sin fila de compañía en el estimate
-- -----------------------------------------------------------------------------
INSERT INTO `estimate_company` (`estimate_id`, `company_id`, `contact_id`, `bid_deadline`, `tag`, `address`)
SELECT
  ebd.`estimate_id`,
  ebd.`company_id`,
  NULL AS `contact_id`,
  ebd.`bid_deadline`,
  ebd.`tag`,
  ebd.`address`
FROM `estimate_bid_deadline` ebd
INNER JOIN (
  SELECT `estimate_id`, `company_id`, MAX(`id`) AS `max_id`
  FROM `estimate_bid_deadline`
  GROUP BY `estimate_id`, `company_id`
) pick ON pick.`max_id` = ebd.`id`
WHERE NOT EXISTS (
  SELECT 1
  FROM `estimate_company` ec0
  WHERE ec0.`estimate_id` <=> ebd.`estimate_id`
    AND ec0.`company_id` <=> ebd.`company_id`
);

-- -----------------------------------------------------------------------------
-- 3) Eliminar tabla antigua
-- -----------------------------------------------------------------------------


DROP TABLE IF EXISTS `estimate_bid_deadline`;
