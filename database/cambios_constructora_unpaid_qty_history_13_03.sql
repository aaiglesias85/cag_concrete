-- Historial de cambios de Unpaid Qty en Payments (cuando se guarda una nota con Override Unpaid Qty)
-- Tabla: invoice_item_unpaid_qty_history

CREATE TABLE invoice_item_unpaid_qty_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_item_id INT NOT NULL,
    old_value DECIMAL(18,6) NULL,
    new_value DECIMAL(18,6) NULL,
    created_at DATETIME NOT NULL,
    user_id INT NULL,
    FOREIGN KEY (invoice_item_id) REFERENCES invoice_item(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
