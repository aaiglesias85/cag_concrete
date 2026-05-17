-- =========================================================================
-- Migración: Soporte multi-estado para Counties + coordenadas para el mapa
-- Fecha: 2026-05-17
--
-- Cambios:
--   1) Crear tabla `state` con los 50 estados de EE. UU. (+ DC) pre-cargados.
--   2) Agregar columnas a `county`:
--        - state_id  (FK a state, requerido a futuro)
--        - latitude  (decimal, opcional, para mapa)
--        - longitude (decimal, opcional, para mapa)
--   3) Backfill: todos los counties existentes -> Georgia.
--   4) Agregar FK county.state_id -> state.id (sin ON DELETE CASCADE).
--
-- Notas:
--   - state_id se deja NULLABLE en esta migración para no romper datos
--     existentes. Una vez confirmado el backfill, se podrá hacer NOT NULL
--     en una segunda corrida (ver bloque comentado al final).
--   - latitude/longitude quedan NULLABLE: los counties sin coordenadas
--     simplemente no se muestran en el widget Measurements hasta que
--     se llenen desde /admin/county (autocomplete Google Places).
-- =========================================================================

START TRANSACTION;

-- -------------------------------------------------------------------------
-- 1) Tabla state
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_state_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------------------
-- 2) Seed: 50 estados + DC
--    Se inserta Georgia (GA) primero para garantizar id=1.
-- -------------------------------------------------------------------------
INSERT INTO `state` (`code`, `name`, `status`) VALUES
('GA', 'Georgia', 1),
('AL', 'Alabama', 1),
('AK', 'Alaska', 1),
('AZ', 'Arizona', 1),
('AR', 'Arkansas', 1),
('CA', 'California', 1),
('CO', 'Colorado', 1),
('CT', 'Connecticut', 1),
('DE', 'Delaware', 1),
('DC', 'District of Columbia', 1),
('FL', 'Florida', 1),
('HI', 'Hawaii', 1),
('ID', 'Idaho', 1),
('IL', 'Illinois', 1),
('IN', 'Indiana', 1),
('IA', 'Iowa', 1),
('KS', 'Kansas', 1),
('KY', 'Kentucky', 1),
('LA', 'Louisiana', 1),
('ME', 'Maine', 1),
('MD', 'Maryland', 1),
('MA', 'Massachusetts', 1),
('MI', 'Michigan', 1),
('MN', 'Minnesota', 1),
('MS', 'Mississippi', 1),
('MO', 'Missouri', 1),
('MT', 'Montana', 1),
('NE', 'Nebraska', 1),
('NV', 'Nevada', 1),
('NH', 'New Hampshire', 1),
('NJ', 'New Jersey', 1),
('NM', 'New Mexico', 1),
('NY', 'New York', 1),
('NC', 'North Carolina', 1),
('ND', 'North Dakota', 1),
('OH', 'Ohio', 1),
('OK', 'Oklahoma', 1),
('OR', 'Oregon', 1),
('PA', 'Pennsylvania', 1),
('RI', 'Rhode Island', 1),
('SC', 'South Carolina', 1),
('SD', 'South Dakota', 1),
('TN', 'Tennessee', 1),
('TX', 'Texas', 1),
('UT', 'Utah', 1),
('VT', 'Vermont', 1),
('VA', 'Virginia', 1),
('WA', 'Washington', 1),
('WV', 'West Virginia', 1),
('WI', 'Wisconsin', 1),
('WY', 'Wyoming', 1);

-- -------------------------------------------------------------------------
-- 3) Columnas nuevas en county
-- -------------------------------------------------------------------------
ALTER TABLE `county`
    ADD COLUMN `state_id` INT(11) NULL DEFAULT NULL AFTER `district_id`,
    ADD COLUMN `latitude` DECIMAL(10, 7) NULL DEFAULT NULL AFTER `state_id`,
    ADD COLUMN `longitude` DECIMAL(10, 7) NULL DEFAULT NULL AFTER `latitude`;

-- -------------------------------------------------------------------------
-- 4) Backfill: todos los counties existentes -> Georgia
-- -------------------------------------------------------------------------
UPDATE `county`
   SET `state_id` = (SELECT `id` FROM `state` WHERE `code` = 'GA' LIMIT 1)
 WHERE `state_id` IS NULL;

-- -------------------------------------------------------------------------
-- 5) FK county.state_id -> state.id
--    RESTRICT en delete/update para evitar borrar estados con counties.
-- -------------------------------------------------------------------------
ALTER TABLE `county`
    ADD KEY `idx_county_state_id` (`state_id`),
    ADD CONSTRAINT `fk_county_state`
        FOREIGN KEY (`state_id`) REFERENCES `state` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE;

COMMIT;

-- =========================================================================
-- (Opcional) Bloque a correr DESPUÉS de verificar el backfill, para forzar
-- que todo county tenga state. Descomentar y ejecutar manualmente cuando
-- estén seguros de que no hay registros sin state_id.
-- =========================================================================
-- ALTER TABLE `county` MODIFY `state_id` INT(11) NOT NULL;
