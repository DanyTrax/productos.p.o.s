ALTER TABLE `zarest_users`
  ADD COLUMN `can_open_register` TINYINT(1) NOT NULL DEFAULT 0 AFTER `store_id`,
  ADD COLUMN `can_close_register` TINYINT(1) NOT NULL DEFAULT 0 AFTER `can_open_register`;

UPDATE `zarest_users`
SET `can_open_register` = 1,
    `can_close_register` = 1
WHERE `role` = 'admin';
