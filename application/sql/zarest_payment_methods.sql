-- Medios de pago configurables (ejecutar una vez en la base pos21 / pos_p21)
CREATE TABLE IF NOT EXISTS `zarest_payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `type_code` varchar(20) NOT NULL DEFAULT 'other',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `legacy_key` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `legacy_key` (`legacy_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `zarest_payment_methods` (`id`, `name`, `type_code`, `sort_order`, `legacy_key`) VALUES
(1, 'Efectivo', 'cash', 0, 0),
(2, 'Tarjeta de crédito', 'card', 1, 1),
(3, 'Cheque', 'cheque', 2, 2);

ALTER TABLE `zarest_payment_methods` AUTO_INCREMENT = 4;
