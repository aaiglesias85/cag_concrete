-- --------------------------------------------------------
-- Cambios: cuotas (quotes) y envío
--
-- 1. estimate_quote (actual) se renombra a estimate_quote_items (ítems de una cuota)
-- 2. Nueva tabla estimate_quote = la cuota (estimate_id, name)
-- 3. estimate_quote_items pasa a tener estimate_quote_id (FK a estimate_quote); se elimina estimate_id
-- 4. Migración: por cada estimate se crea una cuota "Quote 1" y los ítems actuales se asocian a ella
-- 5. Nueva tabla estimate_quote_company = compañías que reciben la cuota (para el envío)
-- --------------------------------------------------------

-- Paso 1: Renombrar tabla actual estimate_quote -> estimate_quote_items
RENAME TABLE `estimate_quote` TO `estimate_quote_items`;

-- Paso 2: Crear nueva tabla estimate_quote (la cuota)
CREATE TABLE `estimate_quote` (
  `id` int(11) NOT NULL,
  `estimate_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `estimate_quote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estimate_quote_estimate_id` (`estimate_id`);

ALTER TABLE `estimate_quote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `estimate_quote`
  ADD CONSTRAINT `Refestimate_quote_estimate` FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Paso 3: Crear cuota "Quote 1" para todos los estimados actuales
INSERT INTO `estimate_quote` (`estimate_id`, `name`)
SELECT `estimate_id`, 'Quote 1' FROM `estimate`;

-- Paso 4: Añadir columna estimate_quote_id a estimate_quote_items y asociar ítems a su cuota "Quote 1"
ALTER TABLE `estimate_quote_items` ADD COLUMN `estimate_quote_id` int(11) DEFAULT NULL AFTER `equation_id`;

UPDATE `estimate_quote_items` i
INNER JOIN `estimate_quote` q ON q.estimate_id = i.estimate_id AND q.name = 'Quote 1'
SET i.estimate_quote_id = q.id;

-- Paso 5: Eliminar estimate_id de estimate_quote_items (primero la FK, luego la columna)
ALTER TABLE `estimate_quote_items` DROP FOREIGN KEY `Refestimate_quote1`;
ALTER TABLE `estimate_quote_items` DROP COLUMN `estimate_id`;

-- Paso 6: Índice y FK de estimate_quote_items -> estimate_quote
ALTER TABLE `estimate_quote_items`
  ADD KEY `Refestimate_quote_items_quote` (`estimate_quote_id`);

ALTER TABLE `estimate_quote_items`
  ADD CONSTRAINT `Refestimate_quote_items_quote` FOREIGN KEY (`estimate_quote_id`) REFERENCES `estimate_quote` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Paso 7: Tabla para envío: compañías que reciben cada cuota
CREATE TABLE `estimate_quote_company` (
  `id` int(11) NOT NULL,
  `estimate_quote_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `estimate_quote_company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Refestimate_quote_company1` (`estimate_quote_id`),
  ADD KEY `Refestimate_quote_company2` (`company_id`);

ALTER TABLE `estimate_quote_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `estimate_quote_company`
  ADD CONSTRAINT `Refestimate_quote_company1` FOREIGN KEY (`estimate_quote_id`) REFERENCES `estimate_quote` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Refestimate_quote_company2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`) ON DELETE CASCADE ON UPDATE CASCADE;
