ALTER TABLE district DROP FOREIGN KEY Refdistrictcountyid;
ALTER TABLE `district` DROP `county_id`;

ALTER TABLE `county` ADD `district_id` INT(11) NULL AFTER `status`, ADD INDEX (`district_id`);

ALTER TABLE `county` ADD CONSTRAINT `Refcountydistrictid` FOREIGN KEY (`district_id`) REFERENCES
    `district`(`district_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;