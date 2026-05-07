-- Agregar campo invoice_date a la tabla invoice
-- Fecha: 2025-01-XX
-- Descripción: Agregar campo invoice_date para almacenar la fecha de la factura editable

ALTER TABLE `invoice` ADD COLUMN `invoice_date` DATE DEFAULT NULL AFTER `end_date`;