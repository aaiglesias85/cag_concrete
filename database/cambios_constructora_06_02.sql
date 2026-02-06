-- Agregar campo retainage a la tabla user
ALTER TABLE `user`
  ADD COLUMN `retainage` tinyint(1) DEFAULT NULL AFTER `bone`;