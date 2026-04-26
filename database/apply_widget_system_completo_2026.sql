-- =============================================================================
-- ÚNICO script a ejecutar en BD para el sistema de widgets (Home) y Tasks.
-- Fecha: 2026-04-26. Hacer copia de seguridad antes.
--
-- Contiene: tablas widgets / rol_widget_access / user_widget_access, datos,
-- migración desde function 40-50, limpieza 41-50 (function 40 = Tasks no se toca)
-- eliminación de `user_widget_preference` (sustituida por `user_widget_access`). No depende de otros .sql; si en el
-- repo hay scripts antiguos (p. ej. `cambios_home_widgets.sql` con funciones 41+),
-- este reemplaza ese enfoque: ejecutar solo este archivo.
--
-- Idempotente en lo posible: CREATE IF NOT EXISTS, INSERT ... ON DUPLICATE.
-- =============================================================================

-- ---------------------------------------------------------------------------
-- 1) Catálogo
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `widgets` (
  `widget_id`    int(11) NOT NULL AUTO_INCREMENT,
  `code`         varchar(64)  NOT NULL,
  `title`        varchar(255) NOT NULL,
  `description`  varchar(500) NOT NULL DEFAULT '',
  `sort_order`   smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (`widget_id`),
  UNIQUE KEY `uq_widgets_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `widgets` (`code`, `title`, `description`, `sort_order`) VALUES
('tasks', 'Tasks', 'Your assigned work and due dates', 0),
('work_schedule', 'Work Schedule', 'Weekly view of field operations and priorities.', 10),
('bid_deadlines', 'Upcoming bid deadlines', 'Projects with critical proposal dates and assigned estimator.', 20),
('estimate_win_loss', 'Estimate win / loss ratio', 'Submitted estimates won vs. lost.', 30),
('estimates_submitted_totals', 'Total estimates — submitted / not submitted', 'Count of submitted vs. draft or pending.', 40),
('estimator_submitted_share', 'Estimator submitted share', 'Share of submitted proposals by estimator.', 50),
('current_month_data_tracking', 'Current month projects (data tracking)', 'Aggregates for the current month from data tracking.', 60),
('invoiced_projects', 'Invoiced projects (period)', 'Billed amount and quick glance of payment total.', 70),
('pay_item_totals', 'Pay item totals (period)', 'Sums of pay item quantities and amounts.', 80),
('invoice_profit_share', 'Invoice / profit share', 'Real profitability vs. invoiced amounts.', 90),
('job_cost_breakdown', 'Job Cost Breakdown', 'Labor, materials, and other direct costs.', 100)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`), `sort_order` = VALUES(`sort_order`);

-- ---------------------------------------------------------------------------
-- 2) Rol: acceso a widgets (coherente con `rol` / `rol_permission`)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rol_widget_access` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `rol_id`      int(11) NOT NULL,
  `widget_id`   int(11) NOT NULL,
  `is_enabled`  tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rol_widget_access` (`rol_id`, `widget_id`),
  CONSTRAINT `fk_rwa_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`rol_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rwa_widget` FOREIGN KEY (`widget_id`) REFERENCES `widgets` (`widget_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- 3) Usuario: acceso a widgets
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_widget_access` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) NOT NULL,
  `widget_id`   int(11) NOT NULL,
  `is_enabled`  tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_widget_access` (`user_id`, `widget_id`),
  CONSTRAINT `fk_uwa_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uwa_widget` FOREIGN KEY (`widget_id`) REFERENCES `widgets` (`widget_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- 4) Migrar rol_permission (view) -> rol_widget_access
--     Requiere que sigan existiendo `function` 40-50 al ejecutar (si ya se borraron, omitir o restaurar dump).
-- ---------------------------------------------------------------------------
INSERT INTO `rol_widget_access` (`rol_id`, `widget_id`, `is_enabled`)
SELECT rp.`rol_id`, w.`widget_id`, CASE WHEN COALESCE(rp.`view_permission`, 0) = 1 THEN 1 ELSE 0 END
FROM `rol_permission` rp
INNER JOIN `function` f ON f.`function_id` = rp.`function_id` AND f.`function_id` BETWEEN 40 AND 50
INNER JOIN `widgets` w ON w.`code` = CASE f.`function_id`
  WHEN 40 THEN 'tasks' WHEN 41 THEN 'work_schedule' WHEN 42 THEN 'bid_deadlines'
  WHEN 43 THEN 'estimate_win_loss' WHEN 44 THEN 'estimates_submitted_totals' WHEN 45 THEN 'estimator_submitted_share'
  WHEN 46 THEN 'current_month_data_tracking' WHEN 47 THEN 'invoiced_projects' WHEN 48 THEN 'pay_item_totals'
  WHEN 49 THEN 'invoice_profit_share' WHEN 50 THEN 'job_cost_breakdown' END
ON DUPLICATE KEY UPDATE `is_enabled` = VALUES(`is_enabled`);

-- ---------------------------------------------------------------------------
-- 5) Migrar user_permission -> user_widget_access
-- ---------------------------------------------------------------------------
INSERT INTO `user_widget_access` (`user_id`, `widget_id`, `is_enabled`)
SELECT up.`user_id`, w.`widget_id`, CASE WHEN COALESCE(up.`view_permission`, 0) = 1 THEN 1 ELSE 0 END
FROM `user_permission` up
INNER JOIN `function` f ON f.`function_id` = up.`function_id` AND f.`function_id` BETWEEN 40 AND 50
INNER JOIN `widgets` w ON w.`code` = CASE f.`function_id`
  WHEN 40 THEN 'tasks' WHEN 41 THEN 'work_schedule' WHEN 42 THEN 'bid_deadlines'
  WHEN 43 THEN 'estimate_win_loss' WHEN 44 THEN 'estimates_submitted_totals' WHEN 45 THEN 'estimator_submitted_share'
  WHEN 46 THEN 'current_month_data_tracking' WHEN 47 THEN 'invoiced_projects' WHEN 48 THEN 'pay_item_totals'
  WHEN 49 THEN 'invoice_profit_share' WHEN 50 THEN 'job_cost_breakdown' END
ON DUPLICATE KEY UPDATE `is_enabled` = VALUES(`is_enabled`);

-- ---------------------------------------------------------------------------
-- 6) Quitar 41-50: menú pasa a function 40 (Tasks) + permisos de módulos; widgets en tablas nuevas.
-- ---------------------------------------------------------------------------
DELETE FROM `user_permission` WHERE `function_id` BETWEEN 41 AND 50;
DELETE FROM `rol_permission`  WHERE `function_id` BETWEEN 41 AND 50;
DELETE FROM `function`         WHERE `function_id` BETWEEN 41 AND 50;

-- ---------------------------------------------------------------------------
-- 7) `user_widget_preference`: ya no se usa (solo `user_widget_access`)
--     Si tuvieras preferencias en la tabla antigua que no se reflejen en
--     user_widget_access, haz copia/merge manual antes. Si no existía, no pasa nada.
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_widget_preference`;

-- ---------------------------------------------------------------------------
-- 8) (Solo entornos que ya tenían el nombre antiguo) renombrar a `rol_widget_access`
--     Ejecutar UNA VEZ si existe `profile_widget_access` y aún no `rol_widget_access`.
-- ---------------------------------------------------------------------------
-- RENAME TABLE `profile_widget_access` TO `rol_widget_access`;

-- Fin del script