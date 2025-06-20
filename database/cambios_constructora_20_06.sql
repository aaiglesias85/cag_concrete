CREATE TABLE user_qbwc_token
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token TEXT,
    user_id INT(11)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `user_qbwc_token` ADD CONSTRAINT `Refuser_qbwc_token1` FOREIGN KEY (`user_id`)
    REFERENCES `user`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


CREATE TABLE sync_queue_qbwc
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    entidad_id INT(11),
    estado VARCHAR(50),
    intentos INT(11),
    created_at DATETIME
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

