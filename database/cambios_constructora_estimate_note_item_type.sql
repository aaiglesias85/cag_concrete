-- --------------------------------------------------------
-- estimate_note_item: agregar campo type ('item' | 'template')
-- --------------------------------------------------------

ALTER TABLE `estimate_note_item`
  ADD COLUMN `type` VARCHAR(20) NOT NULL DEFAULT 'item' AFTER `description`;

-- Valores permitidos: 'item', 'template'. Filas existentes quedan como 'item'.
-- Opcional: restricción CHECK (MySQL 8.0.16+)
-- ALTER TABLE `estimate_note_item`
--   ADD CONSTRAINT `chk_estimate_note_item_type` CHECK (`type` IN ('item', 'template'));
