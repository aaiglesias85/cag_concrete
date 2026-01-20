-- Agregar campo bone a la tabla user
ALTER TABLE `user`
  ADD COLUMN `bone` tinyint(1) DEFAULT NULL AFTER `estimator`;

-- Agregar campo bone a la tabla item
ALTER TABLE `item`
  ADD COLUMN `bone` tinyint(1) DEFAULT NULL AFTER `status`;
