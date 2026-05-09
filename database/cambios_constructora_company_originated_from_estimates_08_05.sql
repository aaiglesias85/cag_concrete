-- Marca compañías creadas desde estimados (E en Librería).
-- Fecha: 2026-05-08
-- Aplicar antes/después del despliegue del código que mapea `Company::$originatedFromEstimates`.

ALTER TABLE `company`
   ADD COLUMN `originated_from_estimates` TINYINT(1) NOT NULL DEFAULT 0
   AFTER `website`;
