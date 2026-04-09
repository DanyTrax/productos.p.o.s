-- Columnas usadas al cerrar caja (pos/SubmitRegister → Register::update_attributes).
-- Si en producción ves error SQL tipo "Unknown column 'closed_at'" o "closed_by",
-- aplica este script en la base del POS.
--
-- MySQL 8.0.12+ / MariaDB 10.3.3+ (IF NOT EXISTS en ADD COLUMN):
ALTER TABLE `zarest_registers` ADD COLUMN IF NOT EXISTS `closed_at` varchar(150) DEFAULT NULL AFTER `note`;
ALTER TABLE `zarest_registers` ADD COLUMN IF NOT EXISTS `closed_by` int(11) DEFAULT NULL AFTER `closed_at`;

-- MySQL 5.7 u otro motor sin IF NOT EXISTS: comprueba antes con
--   SHOW COLUMNS FROM `zarest_registers` LIKE 'closed_at';
-- y ejecuta solo las líneas que falten:
-- ALTER TABLE `zarest_registers` ADD COLUMN `closed_at` varchar(150) DEFAULT NULL AFTER `note`;
-- ALTER TABLE `zarest_registers` ADD COLUMN `closed_by` int(11) DEFAULT NULL AFTER `closed_at`;
