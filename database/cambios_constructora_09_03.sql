-- Función y permisos para el mantenedor
INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('38', 'note_estimate_item', 'Items Notes');

INSERT INTO `rol_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '38');

INSERT INTO `user_permission` (`id`, `view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES (NULL, '1', '1', '1', '1', '1', '38');

-- Tabla estimate_note_item (ítems de nota por estimate)
CREATE TABLE `estimate_note_item` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

-- Relación muchos a muchos: un ítem de quote puede tener varias notas (estimate_note_item)
CREATE TABLE `estimate_quote_item_note` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `estimate_quote_item_id` INT(11) NOT NULL,
    `estimate_note_item_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_quote_item_note` (`estimate_quote_item_id`, `estimate_note_item_id`),
    KEY `estimate_quote_item_id` (`estimate_quote_item_id`),
    KEY `estimate_note_item_id` (`estimate_note_item_id`),
    CONSTRAINT `Refestimate_quote_item_note_quote_item`
        FOREIGN KEY (`estimate_quote_item_id`) REFERENCES `estimate_quote_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `Refestimate_quote_item_note_note`
        FOREIGN KEY (`estimate_note_item_id`) REFERENCES `estimate_note_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

