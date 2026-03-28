-- =============================================================================
-- Cabecera invoice_override_payment + vínculo en invoice_item_override_payment
-- =============================================================================
-- Objetivo:
--   - Nueva tabla invoice_override_payment: un registro por proyecto + fecha
--     (equivalente a lo que se elige en el tab General: project + date).
--   - invoice_item_override_payment pasa a ser las líneas/detalle de esa cabecera
--     mediante invoice_override_payment_id.
--
-- Requisitos: MySQL 5.7+ / MariaDB 10.x. Ejecutar en entorno de mantenimiento.
-- Orden: crear cabecera → añadir columna nullable → poblar cabeceras → asignar FK
--        → quitar start_date/end_date del detalle → NOT NULL → FK explícita.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1) Tabla cabecera
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoice_override_payment` (
  `invoice_override_payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL COMMENT 'FK project.project_id (tab General)',
  `date` date DEFAULT NULL COMMENT 'Fecha de período (tab General); sustituye fechas que antes estaban en cada línea',
  PRIMARY KEY (`invoice_override_payment_id`),
  UNIQUE KEY `uk_invoice_override_payment_project_date` (`project_id`,`date`),
  KEY `idx_invoice_override_payment_project` (`project_id`),
  CONSTRAINT `fk_invoice_override_payment_project`
    FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Cabecera de override payment por proyecto y fecha de período';

-- -----------------------------------------------------------------------------
-- 2) Columna FK en detalle (nullable hasta completar migración)
-- -----------------------------------------------------------------------------
ALTER TABLE `invoice_item_override_payment`
  ADD COLUMN `invoice_override_payment_id` int(11) DEFAULT NULL
    COMMENT 'FK a invoice_override_payment (cabecera)'
    AFTER `id`;

-- Índice para joins y FK
ALTER TABLE `invoice_item_override_payment`
  ADD KEY `idx_invoice_item_override_payment_header` (`invoice_override_payment_id`);

-- -----------------------------------------------------------------------------
-- 3) Poblar cabeceras a partir de datos existentes (distinct proyecto + fecha fin)
-- -----------------------------------------------------------------------------
-- Cada combinación distinta (project_id, end_date) de líneas actuales genera una cabecera.
INSERT INTO `invoice_override_payment` (`project_id`, `date`)
SELECT DISTINCT
  pi.`project_id`,
  iop.`end_date` AS `date`
FROM `invoice_item_override_payment` iop
INNER JOIN `project_item` pi ON pi.`id` = iop.`project_item_id`
WHERE NOT EXISTS (
  SELECT 1
  FROM `invoice_override_payment` hop
  WHERE hop.`project_id` = pi.`project_id`
    AND (hop.`date` <=> iop.`end_date`)
);

-- -----------------------------------------------------------------------------
-- 4) Asignar invoice_override_payment_id en cada línea de detalle
-- -----------------------------------------------------------------------------
UPDATE `invoice_item_override_payment` iop
INNER JOIN `project_item` pi ON pi.`id` = iop.`project_item_id`
INNER JOIN `invoice_override_payment` hop
  ON hop.`project_id` = pi.`project_id`
 AND (hop.`date` <=> iop.`end_date`)
SET iop.`invoice_override_payment_id` = hop.`invoice_override_payment_id`
WHERE iop.`invoice_override_payment_id` IS NULL;

-- -----------------------------------------------------------------------------
-- 5) Detalle: eliminar start_date y end_date (la fecha de período queda en invoice_override_payment.date)
-- -----------------------------------------------------------------------------
-- Requiere quitar el índice que usaba esas columnas (nombre según constructora.sql).
ALTER TABLE `invoice_item_override_payment`
  DROP INDEX `idx_invoice_item_override_payment_dates`;

ALTER TABLE `invoice_item_override_payment`
  DROP COLUMN `start_date`,
  DROP COLUMN `end_date`;

-- -----------------------------------------------------------------------------
-- 6) Comprobar huérfanos (no debería haber filas sin cabecera tras el paso 4)
-- -----------------------------------------------------------------------------
-- Si el siguiente SELECT devuelve > 0, revisar datos antes de forzar NOT NULL:
-- SELECT COUNT(*) FROM invoice_item_override_payment WHERE invoice_override_payment_id IS NULL;

-- -----------------------------------------------------------------------------
-- 7) FK y NOT NULL en invoice_override_payment_id (detalle → cabecera)
-- -----------------------------------------------------------------------------
ALTER TABLE `invoice_item_override_payment`
  MODIFY COLUMN `invoice_override_payment_id` int(11) NOT NULL
    COMMENT 'FK a invoice_override_payment (cabecera)';

ALTER TABLE `invoice_item_override_payment`
  ADD CONSTRAINT `fk_invoice_item_override_payment_header`
    FOREIGN KEY (`invoice_override_payment_id`) REFERENCES `invoice_override_payment` (`invoice_override_payment_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Nota: Ajustar código PHP/Doctrine (entidad InvoiceItemOverridePayment, resolvers,
-- OverridePaymentService, repositorios) para dejar de usar start_date/end_date en
-- detalle y leer la fecha desde InvoiceOverridePayment::date.
-- =============================================================================
