-- Crear tabla user_access_token para manejar tokens de autenticación
CREATE TABLE `user_access_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `expires_at` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token` (`token`),
  CONSTRAINT `fk_user_access_token_user_id` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `user` (`user_id`) 
    ON DELETE CASCADE 
    ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar campos para login de app móvil a la tabla user
ALTER TABLE `user` 
  ADD COLUMN `player_id` varchar(255) DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN `push_token` varchar(255) DEFAULT NULL AFTER `player_id`,
  ADD COLUMN `plataforma` varchar(255) DEFAULT NULL AFTER `push_token`,
  ADD COLUMN `imagen` VARCHAR(255) NULL AFTER `plataforma`;