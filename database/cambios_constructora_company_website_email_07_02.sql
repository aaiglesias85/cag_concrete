-- Company: email y website en pestaña General.

ALTER TABLE company
ADD COLUMN email VARCHAR(255) NULL DEFAULT NULL COMMENT 'Email de la compañía',
ADD COLUMN website VARCHAR(500) NULL DEFAULT NULL COMMENT 'Sitio web de la compañía';
