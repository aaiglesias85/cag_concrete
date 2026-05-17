-- =========================================================================
-- Registra el widget "Measurements" en el catálogo.
-- Fecha: 2026-05-17
--
-- Después de correr este script:
--   1) Los admins deben asignar acceso al widget en `user_widget_access`
--      desde la UI de permisos por usuario.
--   2) Cada usuario decide en su profile si lo muestra (`user_preference_widget`).
-- =========================================================================

INSERT INTO `widgets` (`code`, `title`, `description`, `sort_order`)
SELECT 'measurements',
       'Measurements',
       'Geolocated map of project work and per-employee workload distribution.',
       120
  FROM DUAL
 WHERE NOT EXISTS (
       SELECT 1 FROM `widgets` WHERE `code` = 'measurements'
 );
