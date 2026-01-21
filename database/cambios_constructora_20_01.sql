
-- Agregar campo boned a la tabla project_item
ALTER TABLE `project_item`
  ADD COLUMN `boned` tinyint(1) DEFAULT NULL AFTER `apply_retainage`;
