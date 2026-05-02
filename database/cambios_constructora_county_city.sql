-- Campo city en tabla county (ubicación / ciudad asociada al condado)
ALTER TABLE `county`
  ADD COLUMN `city` VARCHAR(255) NULL DEFAULT NULL AFTER `description`;
