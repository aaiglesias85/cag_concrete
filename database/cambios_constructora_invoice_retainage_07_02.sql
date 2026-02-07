-- Retainage a nivel de invoice (independiente del retainage de pagos).
-- current = suma "Final Amount This Period" de items tipo R del invoice actual.
-- calculated = monto retainage aplicado a este invoice (se imprime en Excel).

ALTER TABLE invoice
ADD COLUMN invoice_current_retainage DECIMAL(18,2) NULL DEFAULT NULL COMMENT 'Suma Final Amount This Period de items R del invoice',
ADD COLUMN invoice_retainage_calculated DECIMAL(18,2) NULL DEFAULT NULL COMMENT 'Retainage $ calculado para este invoice (para imprimir)';
