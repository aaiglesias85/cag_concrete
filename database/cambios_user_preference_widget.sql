-- =============================================================================
-- Preferencias de visualización en Home (dashboard) por usuario.
-- Separa el concepto de:
--   - user_widget_access  → qué asigna el administrador (permiso / disponibilidad)
--   - user_preference_widget → qué elige el usuario mostrar en el Home entre los permitidos
--
-- Fecha: 2026-04-28
-- Ejecutar después de backup. Requiere tablas `user`, `widgets` y `user_widget_access`.
--
-- Migración inicial: una fila por cada fila existente en user_widget_access,
-- con is_visible = 1 (todo encendido en preferencia; equivalencia con el comportamiento
-- previo donde un solo flag gobernaba la visibilidad).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `user_preference_widget` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) NOT NULL,
  `widget_id`   int(11) NOT NULL,
  `is_visible`  tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_preference_widget` (`user_id`, `widget_id`),
  CONSTRAINT `fk_upw_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_upw_widget` FOREIGN KEY (`widget_id`) REFERENCES `widgets` (`widget_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos iniciales: solo widgets que el admin ya tenía habilitados en acceso;
-- en preferencia quedan todos visibles (is_visible = 1), equivalente al comportamiento
-- previo de mostrar en Home lo permitido.
INSERT INTO `user_preference_widget` (`user_id`, `widget_id`, `is_visible`)
SELECT `user_id`, `widget_id`, 1
FROM `user_widget_access`
WHERE `is_enabled` = 1
ON DUPLICATE KEY UPDATE `is_visible` = VALUES(`is_visible`);

-- Notas post-ejecución:
-- 1) Tras desplegar la aplicación, el código debe leer el Home desde user_preference_widget
--    (combinado con user_widget_access). Hasta entonces, el comportamiento legacy puede seguir
--    usando solo user_widget_access.
-- 2) Si necesitáis migración literal “una fila por cada fila de user_widget_access con is_visible=1”
--    (incluidos los que tenían is_enabled=0), quitad el WHERE — no recomendado al separar permiso vs preferencia.
