ALTER TABLE `zarest_users`
  ADD COLUMN `store_ids` VARCHAR(255) NULL AFTER `store_id`;

UPDATE `zarest_users`
SET `store_ids` = CAST(`store_id` AS CHAR)
WHERE (`store_ids` IS NULL OR `store_ids` = '')
  AND `store_id` IS NOT NULL
  AND `store_id` > 0;
