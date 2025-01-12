ALTER TABLE `data_tracking_labor` ADD `role` VARCHAR(255) NULL AFTER `hourly_rate`;

INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('16', 'overhead', 'Overhead Price');
INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`) VALUES
(NULL, '1', '1', '1', '1', '1', '16');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`) VALUES
(NULL, '1', '1', '1', '1', '1', '16');

CREATE TABLE `overhead_price` (
`overhead_id` int(11) NOT NULL,
`name` varchar(255) DEFAULT NULL,
`price` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `overhead_price` CHANGE `overhead_id` `overhead_id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`overhead_id`);

ALTER TABLE `data_tracking` ADD `overhead_price_id` INT(11) NULL AFTER `inspector_id`, ADD INDEX (`overhead_price_id`);
ALTER TABLE `data_tracking` ADD CONSTRAINT `Refoverheadprice25` FOREIGN KEY (`overhead_price_id`)
    REFERENCES `overhead_price`(`overhead_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


ALTER TABLE `data_tracking` ADD `color_used` DECIMAL(18,2) NULL AFTER `overhead_price`, ADD `color_price` DECIMAL(18,2) NULL AFTER `color_used`;

ALTER TABLE `data_tracking` ADD `pending` BOOLEAN NULL AFTER `color_price`;
UPDATE `data_tracking` SET `pending` = '0';