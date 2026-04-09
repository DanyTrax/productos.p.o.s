-- Categorías de gastos (ruta categorie_expences). Si en producción falla la página con error de SQL
-- (tabla o columna inexistente), ejecute este script una vez.
CREATE TABLE IF NOT EXISTS `zarest_categorie_expences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Si la tabla ya existe pero falta created_date (MySQL 8+ / MariaDB 10.3+):
-- ALTER TABLE `zarest_categorie_expences` ADD COLUMN IF NOT EXISTS `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `name`;
