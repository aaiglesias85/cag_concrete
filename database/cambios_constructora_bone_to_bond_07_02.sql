-- Renombrar Bone a Bond: regularización de nomenclatura
-- Bone = Bond en BD, código y textos
-- Ejecutar en orden

-- 1. Tabla item: columna bone → bond
ALTER TABLE `item`
  CHANGE COLUMN `bone` `bond` tinyint(1) DEFAULT NULL;

-- 2. Tabla project_item: columna boned → bonded
ALTER TABLE `project_item`
  CHANGE COLUMN `boned` `bonded` tinyint(1) DEFAULT NULL;

-- 3. Tabla user: columna bone → bond (permiso del usuario)
ALTER TABLE `user`
  CHANGE COLUMN `bone` `bond` tinyint(1) DEFAULT NULL;
