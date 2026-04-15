-- --------------------------------------------------------
-- item: código alfanumérico (code) y nombre en contrato (contract_name)
-- Valores NULL en filas existentes; rellenar desde la aplicación o UPDATE masivo.
-- --------------------------------------------------------

ALTER TABLE `item`
    ADD COLUMN `code` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Código alfanumérico del item' AFTER `name`,
    ADD COLUMN `contract_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nombre del item tal como figura en el contrato' AFTER `code`;
