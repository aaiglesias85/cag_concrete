-- Usuario que creó o modificó por última vez cada entrada de project_notes
START TRANSACTION;

ALTER TABLE `project_notes`
  ADD COLUMN `user_id` INT(11) NULL DEFAULT NULL AFTER `project_id`;

ALTER TABLE `project_notes`
  ADD KEY `idx_project_notes_user_id` (`user_id`),
  ADD CONSTRAINT `fk_project_notes_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
    ON DELETE SET NULL ON UPDATE RESTRICT;

COMMIT;
