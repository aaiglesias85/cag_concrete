-- Idioma preferido del usuario en la app (para chat, notificaciones, fallback).
-- Opcional: el flujo de mensajes ya usa el lang que la app envía en cada petición;
-- este campo sirve para persistir la preferencia, notificaciones push y fallback.

ALTER TABLE `user`
  ADD COLUMN `preferred_lang` char(2) DEFAULT 'es' COMMENT 'es|en - idioma preferido en la app' AFTER `imagen`;
