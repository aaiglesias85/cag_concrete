-- Payments: estado manual abierto/cerrado por línea (toggle ítem), sin alterar cantidades.
-- Tabla: invoice_item

ALTER TABLE invoice_item
ADD COLUMN is_closed_manual TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = ítem marcado cerrado en Payments (solo UI/estado)' AFTER unpaid_qty;
