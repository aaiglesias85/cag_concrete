-- Override de unpaid_qty por línea de proyecto (project_item) y rango de vigencia.
-- Vigencia: [start_date, end_date] define el periodo en el que aplica el override.
-- Historial de cambios en invoice_item_override_unpaid_qty_history (mismo patrón que invoice_item_unpaid_qty_history / invoice_item_override_payment_history).

CREATE TABLE `invoice_item_override_unpaid_qty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_item_id` int(11) NOT NULL,
  `unpaid_qty` decimal(18,6) NOT NULL COMMENT 'Cantidad no pagada sobreescrita (agregado); se sincroniza con la última entrada del historial',
  `start_date` date DEFAULT NULL COMMENT 'Inicio de vigencia del override (opcional; NULL = sin filtro de fechas)',
  `end_date` date DEFAULT NULL COMMENT 'Fin de vigencia del override (opcional; NULL = sin filtro de fechas)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_item_override_unpaid_qty_project_item` (`project_item_id`),
  KEY `idx_invoice_item_override_unpaid_qty_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_invoice_item_override_unpaid_qty_project_item` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Override de unpaid qty agregado por project_item y rango de fechas';

CREATE TABLE `invoice_item_override_unpaid_qty_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_item_override_unpaid_qty_id` int(11) NOT NULL,
  `old_value` decimal(18,6) DEFAULT NULL,
  `new_value` decimal(18,6) DEFAULT NULL,
  `note` longtext DEFAULT NULL COMMENT 'Nota (HTML) por entrada; permite N notas como en invoice_item_notes / Payments',
  `created_at` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_item_override_unpaid_qty_history_parent` (`invoice_item_override_unpaid_qty_id`),
  CONSTRAINT `fk_invoice_item_override_unpaid_qty_history_parent` FOREIGN KEY (`invoice_item_override_unpaid_qty_id`) REFERENCES `invoice_item_override_unpaid_qty` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_item_override_unpaid_qty_history_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historial de unpaid_qty con nota por fila (N notas por override)';

-- Migración si las tablas ya existían:
-- ALTER TABLE `invoice_item_override_unpaid_qty` DROP COLUMN `note`;
-- ALTER TABLE `invoice_item_override_unpaid_qty_history` ADD COLUMN `note` longtext DEFAULT NULL COMMENT 'Nota (HTML) por entrada' AFTER `new_value`;
