

CREATE TABLE race
(
    race_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50),
    description VARCHAR(255),
    classification VARCHAR(255)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

  --
-- Volcado de datos para la tabla `race`
--

INSERT INTO `race` (`race_id`, `code`, `description`, `classification`) VALUES
(1, 'As-Ind', 'Asian-Indian', 'Asian or Pacific Islander'),
(2, 'As-Pac', 'Asian-Pacific', 'Asian or Pacific Islander'),
(3, 'Blk', 'Black', 'Black (not of Hispanic origin)'),
(4, 'White', 'White', 'Not a minority'),
(5, 'His', 'Hispanic', 'Hispanic'),
(6, 'Na/Am', 'Native American', 'American Indian or Native American'),
(7, 'Oth', 'Other', 'Not a minority');

  INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('34', 'race', 'Races');

  INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`) 
  VALUES (NULL, '1', '1', '1', '1', '1', '34');

  INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`) 
  VALUES (NULL, '1', '1', '1', '1', '1', '34');


ALTER TABLE `employee` ADD `address` TEXT NULL AFTER `color`,
 ADD `phone` VARCHAR(50) NULL AFTER `address`,
  ADD `cert_rate_type` VARCHAR(255) NULL AFTER `phone`,
   ADD `social_security_number` VARCHAR(50) NULL AFTER `cert_rate_type`,
    ADD `apprentice_percentage` DECIMAL(18,2) NULL AFTER `social_security_number`,
     ADD `work_code` VARCHAR(50) NULL AFTER `apprentice_percentage`,
      ADD `gender` VARCHAR(255) NULL AFTER `work_code`;


ALTER TABLE `employee` ADD `race_id` INT(11) NULL AFTER `gender`, ADD INDEX (`race_id`);

ALTER TABLE `employee` ADD CONSTRAINT `Refemployee1` FOREIGN KEY (`race_id`)
    REFERENCES `race`(`race_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


ALTER TABLE `employee` ADD `date_hired` DATE NULL AFTER `race_id`,
 ADD `date_terminated` DATE NULL AFTER `date_hired`,
  ADD `reason_terminated` VARCHAR(255) NULL AFTER `date_terminated`,
   ADD `time_card_notes` VARCHAR(255) NULL AFTER `reason_terminated`,
    ADD `regular_rate_per_hour` DECIMAL(18,2) NULL AFTER `time_card_notes`,
     ADD `overtime_rate_per_hour` DECIMAL(18,2) NULL AFTER `regular_rate_per_hour`,
      ADD `special_rate_per_hour` DECIMAL(18,2) NULL AFTER `overtime_rate_per_hour`,
       ADD `trade_licenses_info` TEXT NULL AFTER `special_rate_per_hour`,
        ADD `notes` TEXT NULL AFTER `trade_licenses_info`,
         ADD `is_osha_10_certified` BOOLEAN NULL AFTER `notes`,
          ADD `is_veteran` BOOLEAN NULL AFTER `is_osha_10_certified`;


ALTER TABLE `employee` ADD `status` BOOLEAN NULL AFTER `is_veteran`;
UPDATE `employee` SET `status` = '1';