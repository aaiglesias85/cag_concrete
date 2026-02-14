-- Mensajería interna entre usuarios de la app con traducción automática es/en
-- Cada conversación es entre dos usuarios; los mensajes guardan texto original + versión es y en.

-- --------------------------------------------------------
-- Conversación: agrupa el chat entre dos usuarios (user_id menor primero para unicidad)
-- --------------------------------------------------------
CREATE TABLE `message_conversation` (
  `conversation_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL COMMENT 'user_id menor del par',
  `user2_id` int(11) NOT NULL COMMENT 'user_id mayor del par',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL COMMENT 'Última actividad (envío de mensaje)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `message_conversation`
  ADD PRIMARY KEY (`conversation_id`),
  ADD UNIQUE KEY `UQ_conversation_users` (`user1_id`, `user2_id`),
  ADD KEY `IDX_conversation_user1` (`user1_id`),
  ADD KEY `IDX_conversation_user2` (`user2_id`),
  ADD KEY `IDX_conversation_updated_at` (`updated_at`);

ALTER TABLE `message_conversation`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `message_conversation`
  ADD CONSTRAINT `FK_conversation_user1` FOREIGN KEY (`user1_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_conversation_user2` FOREIGN KEY (`user2_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;


-- --------------------------------------------------------
-- Mensaje: texto original + versiones es/en para traducción según idioma del usuario
-- --------------------------------------------------------
CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body_original` text NOT NULL COMMENT 'Texto tal como lo escribió el remitente',
  `source_lang` char(2) NOT NULL DEFAULT 'es' COMMENT 'es|en - idioma del body_original',
  `body_es` text DEFAULT NULL COMMENT 'Versión en español (original o traducida)',
  `body_en` text DEFAULT NULL COMMENT 'Versión en inglés (original o traducida)',
  `created_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL COMMENT 'Cuando el destinatario leyó el mensaje'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `IDX_message_conversation` (`conversation_id`),
  ADD KEY `IDX_message_sender` (`sender_id`),
  ADD KEY `IDX_message_created_at` (`created_at`);

ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `message`
  ADD CONSTRAINT `FK_message_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `message_conversation` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
