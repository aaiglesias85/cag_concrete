-- Nota enriquecida (HTML) por adjunto en las cuatro tablas de attachments
START TRANSACTION;

ALTER TABLE `project_attachment`
  ADD COLUMN `note` TEXT NULL DEFAULT NULL AFTER `file`;

ALTER TABLE `estimate_attachment`
  ADD COLUMN `note` TEXT NULL DEFAULT NULL AFTER `file`;

ALTER TABLE `invoice_attachment`
  ADD COLUMN `note` TEXT NULL DEFAULT NULL AFTER `file`;

ALTER TABLE `data_tracking_attachment`
  ADD COLUMN `note` TEXT NULL DEFAULT NULL AFTER `file`;

COMMIT;
