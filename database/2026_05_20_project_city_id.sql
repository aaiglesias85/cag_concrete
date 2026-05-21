-- Campo city_id en project (ubicación tipo City del catálogo county)
START TRANSACTION;

ALTER TABLE `project`
  ADD COLUMN `city_id` INT(11) NULL DEFAULT NULL AFTER `county`;

ALTER TABLE `project`
  ADD KEY `idx_project_city_id` (`city_id`),
  ADD CONSTRAINT `fk_project_city`
    FOREIGN KEY (`city_id`) REFERENCES `county` (`county_id`)
    ON DELETE SET NULL ON UPDATE RESTRICT;

COMMIT;
