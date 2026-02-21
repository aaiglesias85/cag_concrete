ALTER TABLE `user` ADD `chat` BOOLEAN NULL AFTER `retainage`;

-- agregar columna translated_at a la tabla message
ALTER TABLE `message` ADD `translated_at` DATETIME NULL AFTER `created_at`;