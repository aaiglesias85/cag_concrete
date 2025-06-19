ALTER TABLE `estimate` ADD `bid_description` TEXT NULL AFTER `sector`,
    ADD `bid_instructions` TEXT NULL AFTER `bid_description`,
    ADD `plan_link` TEXT NULL AFTER `bid_instructions`;