-- Override agregado de paid_qty por línea de proyecto (project_item) y rango de vigencia.
-- Vigencia: [start_date, end_date] define el periodo en el que aplica el override (p. ej. alineado a invoices incluidos en el corte).
-- Quién cambió qué y cuándo va en invoice_item_override_payment_history (mismo patrón que invoice_item_unpaid_qty_history).

CREATE TABLE `invoice_item_override_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_item_id` int(11) NOT NULL,
  `paid_qty` decimal(18,6) NOT NULL COMMENT 'Cantidad pagada sobreescrita (agregado)',
  `start_date` date DEFAULT NULL COMMENT 'Inicio de vigencia del override (opcional; NULL = sin filtro de fechas)',
  `end_date` date DEFAULT NULL COMMENT 'Fin de vigencia del override (opcional; NULL = sin filtro de fechas)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_item_override_payment_project_item` (`project_item_id`),
  KEY `idx_invoice_item_override_payment_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_invoice_item_override_payment_project_item` FOREIGN KEY (`project_item_id`) REFERENCES `project_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Override de paid qty agregado por project_item y rango de fechas';

-- Historial de cambios de override (old/new + usuario + fecha), análogo a invoice_item_unpaid_qty_history.
CREATE TABLE `invoice_item_override_payment_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_item_override_payment_id` int(11) NOT NULL,
  `old_value` decimal(18,6) DEFAULT NULL,
  `new_value` decimal(18,6) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_item_override_payment_history_parent` (`invoice_item_override_payment_id`),
  CONSTRAINT `fk_invoice_item_override_payment_history_parent` FOREIGN KEY (`invoice_item_override_payment_id`) REFERENCES `invoice_item_override_payment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_item_override_payment_history_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historial de cambios de paid_qty en invoice_item_override_payment';

-- Si la tabla ya existía con start_date/end_date NOT NULL (versión anterior del script), o para alinear con filtros desde/hasta opcionales:
-- en instalaciones nuevas el CREATE de arriba ya define NULL; este ALTER es redundante pero seguro.
ALTER TABLE `invoice_item_override_payment`
  MODIFY COLUMN `start_date` date DEFAULT NULL COMMENT 'Inicio de vigencia del override (opcional; NULL = sin filtro de fechas)',
  MODIFY COLUMN `end_date` date DEFAULT NULL COMMENT 'Fin de vigencia del override (opcional; NULL = sin filtro de fechas)';

-- Campos opcionales a valorar más adelante:
-- - project_id redundante en la tabla principal para filtros por proyecto.
-- - Si hace falta auditar cambios de fechas o más campos, ampliar esta tabla de historial (p. ej. columnas adicionales o old/new en texto).

-- override payment (permiso función 39)
INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('39', 'override_payment', 'Override Payment');

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '39');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '39');
