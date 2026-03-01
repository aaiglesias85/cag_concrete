-- Eliminar mensajes y chats (estilo WhatsApp)
-- - deleted_for_everyone_at: mensaje eliminado "para todos" (placeholder "Este mensaje fue eliminado")
-- - deleted_for_user_ids: JSON array de user_id que eliminaron el mensaje "para mí"
-- - hidden_for_user_ids: JSON array de user_id que ocultaron el chat de su lista

ALTER TABLE `message`
  ADD COLUMN `deleted_for_everyone_at` datetime DEFAULT NULL COMMENT 'Si set: mensaje eliminado para todos (mostrar placeholder)' AFTER `read_at`,
  ADD COLUMN `deleted_for_user_ids` json DEFAULT NULL COMMENT 'Array de user_id que eliminaron el mensaje para sí mismos' AFTER `deleted_for_everyone_at`;

ALTER TABLE `message_conversation`
  ADD COLUMN `hidden_for_user_ids` json DEFAULT NULL COMMENT 'Array de user_id que ocultaron el chat de su lista' AFTER `updated_at`;
