-- --------------------------------------------------------
-- PUNCH List en data_tracking_item: cantidad no facturable (costo / pérdida).
-- Total quantity = quantity (columna existente). Normal = quantity - punch_quantity.
-- --------------------------------------------------------

ALTER TABLE `data_tracking_item`
    ADD COLUMN `punch_quantity` DECIMAL(18, 6) NOT NULL DEFAULT 0.000000 AFTER `quantity`;


ALTER TABLE `data_tracking_item` CHANGE `punch_quantity` `punch_quantity` DECIMAL(18,6) NOT NULL DEFAULT '0';