ALTER TABLE `project` ADD `retainage` BOOLEAN NULL AFTER `concrete_time_period_unit`,
 ADD `retainage_percentage` DOUBLE(18,2) NULL AFTER `retainage`,
  ADD `retainage_adjustment_percentage` DOUBLE(18,2) NULL AFTER `retainage_percentage`,
   ADD `retainage_adjustment_completion` DOUBLE(18,2) NULL AFTER `retainage_adjustment_percentage`;
