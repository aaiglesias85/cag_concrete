CREATE TABLE data_tracking_subcontract
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    quantity   DECIMAL(18,2),
    price      DECIMAL(18,2),
    notes      TEXT,
    data_tracking_id INT,
    item_id INT
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE INDEX `Ref63452` ON data_tracking_subcontract (data_tracking_id);

ALTER TABLE data_tracking_subcontract
ADD CONSTRAINT `Refdatatrackingsubcontract35` FOREIGN KEY (data_tracking_id) REFERENCES data_tracking (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

CREATE INDEX `Ref63451` ON data_tracking_subcontract (item_id);

ALTER TABLE data_tracking_subcontract
    ADD CONSTRAINT `Refdatatrackingsubcontract36` FOREIGN KEY (item_id) REFERENCES item (item_id) ON DELETE NO ACTION ON UPDATE NO ACTION;