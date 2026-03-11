-- --------------------------------------------------------
-- estimate_template_note: notas de tipo template asociadas al estimate
-- Un estimate puede tener N notas template (estimate_note_item con type = 'template')
-- --------------------------------------------------------

CREATE TABLE `estimate_template_note` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `estimate_id` INT(11) NOT NULL,
    `estimate_note_item_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_estimate_template_note` (`estimate_id`, `estimate_note_item_id`),
    KEY `idx_estimate_template_note_estimate_id` (`estimate_id`),
    KEY `idx_estimate_template_note_note_id` (`estimate_note_item_id`),
    CONSTRAINT `Refestimate_template_note_estimate`
        FOREIGN KEY (`estimate_id`) REFERENCES `estimate` (`estimate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `Refestimate_template_note_note`
        FOREIGN KEY (`estimate_note_item_id`) REFERENCES `estimate_note_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
