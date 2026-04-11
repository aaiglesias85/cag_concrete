-- --------------------------------------------------------
-- estimate_county: varios condados por estimate (N:N)
-- Sin UNIQUE en (estimate_id, county_id) para evitar error 1062.
-- --------------------------------------------------------

CREATE TABLE `estimate_county` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `estimate_id` INT(11) NOT NULL,
    `county_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_estimate_county_estimate_county` (`estimate_id`, `county_id`),
    KEY `idx_estimate_county_estimate_id` (`estimate_id`),
    KEY `idx_estimate_county_county_id` (`county_id`),
    CONSTRAINT `Refestimate_county_estimate`
        FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `Refestimate_county_county`
        FOREIGN KEY (`county_id`) REFERENCES `county` (`county_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Copiar el condado principal que tenía cada estimate en estimate.county_id
INSERT INTO `estimate_county` (`estimate_id`, `county_id`)
SELECT `estimate_id`, `county_id`
FROM `estimate`
WHERE `county_id` IS NOT NULL;
