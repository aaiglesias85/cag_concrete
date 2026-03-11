-- --------------------------------------------------------
-- estimate_note_item: cambiar columna description a TEXT
-- --------------------------------------------------------

ALTER TABLE `estimate_note_item`
  MODIFY COLUMN `description` TEXT DEFAULT NULL;
