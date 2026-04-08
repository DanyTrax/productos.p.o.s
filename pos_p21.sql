-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-11-2020 a las 23:15:27
-- Versión del servidor: 5.7.24
-- Versión de PHP: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pos_p21`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_categories`
--

CREATE TABLE `zarest_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_categories`
--

INSERT INTO `zarest_categories` (`id`, `name`, `created_at`) VALUES
(22, 'Bebidas', '2020-10-29 10:54:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_categorie_expences`
--

CREATE TABLE `zarest_categorie_expences` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_combo_items`
--

CREATE TABLE `zarest_combo_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_customers`
--

CREATE TABLE `zarest_customers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `discount` varchar(5) DEFAULT NULL,
  `created_at` varchar(150) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_customers`
--

INSERT INTO `zarest_customers` (`id`, `name`, `phone`, `email`, `discount`, `created_at`) VALUES
(11, 'gorchor', '948445199', 'gorchor@gmail.com', '', '2020-10-29 10:50:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_expences`
--

CREATE TABLE `zarest_expences` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(150) NOT NULL,
  `note` text,
  `amount` float NOT NULL,
  `attachment` varchar(200) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_holds`
--

CREATE TABLE `zarest_holds` (
  `id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `time` varchar(10) NOT NULL,
  `register_id` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `waiter_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_holds`
--

INSERT INTO `zarest_holds` (`id`, `number`, `time`, `register_id`, `table_id`, `waiter_id`, `customer_id`) VALUES
(267, 1, '11:01', 60, 0, 0, 11),
(275, 2, '11:15', 61, 44, NULL, NULL),
(271, 1, '11:15', 61, 0, 0, 0),
(274, 1, '11:15', 61, 44, 0, 11),
(276, 3, '11:15', 61, 44, 0, 0),
(280, 5, '11:21', 61, 44, 0, 0),
(278, 4, '11:15', 61, 44, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_payements`
--

CREATE TABLE `zarest_payements` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `paid` float NOT NULL,
  `paidmethod` varchar(300) NOT NULL,
  `created_by` varchar(60) NOT NULL,
  `register_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `waiter_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_posales`
--

CREATE TABLE `zarest_posales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `price` float NOT NULL,
  `qt` int(6) NOT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `options` text,
  `time` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_products`
--

CREATE TABLE `zarest_products` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(25) NOT NULL,
  `category` varchar(20) NOT NULL,
  `cost` float NOT NULL,
  `tax` varchar(11) DEFAULT NULL,
  `description` mediumtext,
  `price` float NOT NULL,
  `photo` varchar(200) NOT NULL,
  `photothumb` varchar(500) DEFAULT NULL,
  `color` varchar(10) NOT NULL,
  `created_at` varchar(30) DEFAULT NULL,
  `modified_at` varchar(30) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `alertqt` int(10) DEFAULT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `unit` varchar(100) DEFAULT NULL,
  `taxmethod` tinyint(4) DEFAULT NULL,
  `h_stores` varchar(300) DEFAULT NULL,
  `options` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_purchases`
--

CREATE TABLE `zarest_purchases` (
  `id` int(11) NOT NULL,
  `ref` varchar(11) NOT NULL,
  `date` date NOT NULL,
  `total` float DEFAULT NULL,
  `attachement` varchar(200) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `store_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `note` mediumtext,
  `modified_at` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_purchase_items`
--

CREATE TABLE `zarest_purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qt` int(10) NOT NULL,
  `cost` float NOT NULL,
  `subtot` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_registers`
--

CREATE TABLE `zarest_registers` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(4) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cash_total` float DEFAULT NULL,
  `cash_sub` float DEFAULT NULL,
  `cc_total` float DEFAULT NULL,
  `cc_sub` float DEFAULT NULL,
  `cheque_total` float DEFAULT NULL,
  `cheque_sub` float DEFAULT NULL,
  `cash_inhand` float DEFAULT NULL,
  `note` text,
  `closed_at` varchar(150) DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `store_id` int(11) NOT NULL,
  `waiterscih` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_registers`
--

INSERT INTO `zarest_registers` (`id`, `date`, `status`, `user_id`, `cash_total`, `cash_sub`, `cc_total`, `cc_sub`, `cheque_total`, `cheque_sub`, `cash_inhand`, `note`, `closed_at`, `closed_by`, `store_id`, `waiterscih`) VALUES
(60, '2020-10-29 15:40:03', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 100, NULL, NULL, NULL, 1, ''),
(61, '2020-10-29 16:01:00', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 100, NULL, NULL, NULL, 5, '8,20,');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_sales`
--

CREATE TABLE `zarest_sales` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `clientname` varchar(50) NOT NULL,
  `tax` varchar(5) DEFAULT NULL,
  `discount` varchar(10) DEFAULT NULL,
  `subtotal` varchar(15) NOT NULL,
  `total` float NOT NULL,
  `created_at` date NOT NULL,
  `modified_at` varchar(150) DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `totalitems` int(20) NOT NULL,
  `paid` varchar(15) DEFAULT NULL,
  `paidmethod` varchar(700) DEFAULT NULL,
  `taxamount` float DEFAULT NULL,
  `discountamount` float DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `firstpayement` float DEFAULT NULL,
  `waiter_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_sale_items`
--

CREATE TABLE `zarest_sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `price` float NOT NULL,
  `qt` int(6) NOT NULL,
  `subtotal` varchar(20) NOT NULL,
  `date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_settings`
--

CREATE TABLE `zarest_settings` (
  `id` int(11) NOT NULL,
  `companyname` varchar(100) NOT NULL,
  `logo` varchar(200) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `keyboard` tinyint(1) NOT NULL,
  `receiptheader` text,
  `receiptfooter` text NOT NULL,
  `theme` varchar(20) NOT NULL,
  `discount` varchar(5) DEFAULT NULL,
  `tax` varchar(5) DEFAULT NULL,
  `timezone` varchar(400) DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  `stripe` tinyint(4) DEFAULT NULL,
  `stripe_secret_key` varchar(150) DEFAULT NULL,
  `stripe_publishable_key` varchar(150) DEFAULT NULL,
  `decimals` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_settings`
--

INSERT INTO `zarest_settings` (`id`, `companyname`, `logo`, `phone`, `currency`, `keyboard`, `receiptheader`, `receiptfooter`, `theme`, `discount`, `tax`, `timezone`, `language`, `stripe`, `stripe_secret_key`, `stripe_publishable_key`, `decimals`) VALUES
(1, 'Platea21', NULL, '+51948445199', 'USD', 1, NULL, 'Thank you for your business', 'Light', NULL, '12%', 'America/Lima', 'spanish', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_stocks`
--

CREATE TABLE `zarest_stocks` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `quantity` int(10) DEFAULT NULL,
  `price` float DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_stores`
--

CREATE TABLE `zarest_stores` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `email` varchar(40) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `adresse` varchar(400) DEFAULT NULL,
  `footer_text` varchar(400) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `country` varchar(20) DEFAULT NULL,
  `created_at` varchar(200) NOT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_stores`
--

INSERT INTO `zarest_stores` (`id`, `name`, `email`, `phone`, `adresse`, `footer_text`, `city`, `country`, `created_at`, `status`) VALUES
(1, 'Dar el web', 'default@store.com', '+12345678', 'adresse', 'Custome Footer for dal web store', 'zarzis', 'tunisia', '2016-05-10 12:44:33', 1),
(5, 'Platea21', 'gorchor@gmail.com', '948445199', 'Tacna', 'Platea21 - Contacto +51948445199', 'Tacna', 'Perú', '2020-10-29 10:53:13', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_suppliers`
--

CREATE TABLE `zarest_suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `note` mediumtext,
  `created_at` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_tables`
--

CREATE TABLE `zarest_tables` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `time` varchar(10) DEFAULT NULL,
  `store_id` int(11) NOT NULL,
  `checked` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_tables`
--

INSERT INTO `zarest_tables` (`id`, `name`, `zone_id`, `status`, `time`, `store_id`, `checked`) VALUES
(44, '01', 9, 1, '11:15', 5, NULL),
(45, '02', 9, 0, '', 5, '2020-10-29 11:01:32'),
(46, '01', 10, NULL, NULL, 5, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_users`
--

CREATE TABLE `zarest_users` (
  `id` int(11) NOT NULL,
  `username` varchar(45) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `hashed_password` varchar(128) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `last_active` varchar(50) DEFAULT NULL,
  `avatar` varchar(200) DEFAULT NULL,
  `created_at` varchar(300) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_users`
--

INSERT INTO `zarest_users` (`id`, `username`, `firstname`, `lastname`, `hashed_password`, `email`, `role`, `last_active`, `avatar`, `created_at`, `store_id`) VALUES
(1, 'admin', 'admin', 'Doe', '8091d35190efa5d080867aa44c596d0f269f2d3faee949c7a056fbef12a8a67ffbc7a34efe4ac206b15a2747ca63b6c9684a98d94f376aa929e4ebe04a50c16b', 'admin@dar-elweb.com', 'admin', '2020-10-29 11:06:45', '9fff9cc26e539214e9a5fd3b6a10cde9.jpg', '2020-10-29 11:08:23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_variations`
--

CREATE TABLE `zarest_variations` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `price` float DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_waiters`
--

CREATE TABLE `zarest_waiters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `created_at` varchar(150) DEFAULT NULL,
  `store_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_waiters`
--

INSERT INTO `zarest_waiters` (`id`, `name`, `phone`, `email`, `created_at`, `store_id`) VALUES
(8, 'camarero01', '948445199', 'gorchor@gmail.com', '2020-10-29 10:53:58', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_warehouses`
--

CREATE TABLE `zarest_warehouses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adresse` varchar(400) DEFAULT NULL,
  `created_at` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zarest_zones`
--

CREATE TABLE `zarest_zones` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zarest_zones`
--

INSERT INTO `zarest_zones` (`id`, `store_id`, `name`) VALUES
(9, 5, 'VIP'),
(10, 5, 'Niños'),
(11, 5, 'Comidas');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `zarest_categories`
--
ALTER TABLE `zarest_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_categorie_expences`
--
ALTER TABLE `zarest_categorie_expences`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_combo_items`
--
ALTER TABLE `zarest_combo_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_customers`
--
ALTER TABLE `zarest_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_expences`
--
ALTER TABLE `zarest_expences`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_holds`
--
ALTER TABLE `zarest_holds`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_payements`
--
ALTER TABLE `zarest_payements`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_posales`
--
ALTER TABLE `zarest_posales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_products`
--
ALTER TABLE `zarest_products`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_purchases`
--
ALTER TABLE `zarest_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_purchase_items`
--
ALTER TABLE `zarest_purchase_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_registers`
--
ALTER TABLE `zarest_registers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_sales`
--
ALTER TABLE `zarest_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_sale_items`
--
ALTER TABLE `zarest_sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_settings`
--
ALTER TABLE `zarest_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_stocks`
--
ALTER TABLE `zarest_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_stores`
--
ALTER TABLE `zarest_stores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_suppliers`
--
ALTER TABLE `zarest_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_tables`
--
ALTER TABLE `zarest_tables`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_users`
--
ALTER TABLE `zarest_users`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_waiters`
--
ALTER TABLE `zarest_waiters`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_warehouses`
--
ALTER TABLE `zarest_warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zarest_zones`
--
ALTER TABLE `zarest_zones`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `zarest_categories`
--
ALTER TABLE `zarest_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `zarest_categorie_expences`
--
ALTER TABLE `zarest_categorie_expences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `zarest_combo_items`
--
ALTER TABLE `zarest_combo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `zarest_customers`
--
ALTER TABLE `zarest_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `zarest_expences`
--
ALTER TABLE `zarest_expences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `zarest_holds`
--
ALTER TABLE `zarest_holds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=281;

--
-- AUTO_INCREMENT de la tabla `zarest_payements`
--
ALTER TABLE `zarest_payements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `zarest_posales`
--
ALTER TABLE `zarest_posales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1543;

--
-- AUTO_INCREMENT de la tabla `zarest_products`
--
ALTER TABLE `zarest_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT de la tabla `zarest_purchases`
--
ALTER TABLE `zarest_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zarest_purchase_items`
--
ALTER TABLE `zarest_purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zarest_registers`
--
ALTER TABLE `zarest_registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `zarest_sales`
--
ALTER TABLE `zarest_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `zarest_sale_items`
--
ALTER TABLE `zarest_sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1023;

--
-- AUTO_INCREMENT de la tabla `zarest_settings`
--
ALTER TABLE `zarest_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `zarest_stocks`
--
ALTER TABLE `zarest_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `zarest_stores`
--
ALTER TABLE `zarest_stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `zarest_suppliers`
--
ALTER TABLE `zarest_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `zarest_tables`
--
ALTER TABLE `zarest_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `zarest_users`
--
ALTER TABLE `zarest_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `zarest_waiters`
--
ALTER TABLE `zarest_waiters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `zarest_warehouses`
--
ALTER TABLE `zarest_warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `zarest_zones`
--
ALTER TABLE `zarest_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
