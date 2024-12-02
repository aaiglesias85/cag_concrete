CREATE TABLE data_tracking_conc_vendor
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    conc_vendor       VARCHAR(255),
    total_conc_used   DECIMAL(18,2),
    conc_price      DECIMAL(18,2),
    data_tracking_id INT
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

CREATE INDEX `Ref6345` ON data_tracking_conc_vendor (data_tracking_id);

ALTER TABLE data_tracking_conc_vendor
    ADD CONSTRAINT `Refdatatrackingconcvendor35` FOREIGN KEY (data_tracking_id) REFERENCES data_tracking (id) ON DELETE NO ACTION ON UPDATE NO ACTION;