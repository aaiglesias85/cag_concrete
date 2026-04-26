-- Home Widgets: Capa 1 (permisos) + Capa 2 (preferencias de usuario)
-- Fecha: 2026-04-26

-- ============================================================
-- CAPA 1: Nuevas funciones para los widgets del Home
-- Los IDs 41-51 se reservan para widgets.
-- El widget 'tasks' reutiliza el function_id=40 existente.
-- ============================================================

INSERT INTO `function` (`function_id`, `url`, `description`) VALUES
(41, 'widget_work_schedule',          'Widget: Work Schedule'),
(42, 'widget_bid_deadlines',          'Widget: Upcoming Bid Deadlines'),
(43, 'widget_estimate_win_loss',      'Widget: Estimate Win/Loss Ratio'),
(44, 'widget_estimates_submitted',    'Widget: Estimates Submitted'),
(45, 'widget_estimator_share',        'Widget: Estimator Submitted Share'),
(46, 'widget_current_month_projects', 'Widget: Current Month Projects'),
(47, 'widget_invoiced_projects',      'Widget: Invoiced Projects'),
(48, 'widget_pay_item_totals',        'Widget: Pay Item Totals'),
(49, 'widget_invoice_profit_share',   'Widget: Invoice/Profit Share'),
(50, 'widget_job_cost_breakdown',     'Widget: Job Cost Breakdown');

-- ============================================================
-- CAPA 2: Preferencias del usuario (qué widgets quiere ver)
-- El usuario activa/desactiva solo los widgets que tiene permiso.
-- ============================================================

CREATE TABLE `user_widget_preference` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)      NOT NULL,
  `widget_url` varchar(100) NOT NULL,
  `is_active`  tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_widget` (`user_id`, `widget_url`),
  CONSTRAINT `fk_uwp_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
