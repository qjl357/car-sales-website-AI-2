-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-05-11 14:53:06
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `car_sales`
--

-- --------------------------------------------------------

--
-- 表的结构 `sellers`
--

CREATE TABLE `sellers` (
  `id` int(11) NOT NULL,
  `fullName` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `sellers`
--

INSERT INTO `sellers` (`id`, `fullName`, `address`, `phone`, `email`, `password`, `created_at`, `username`) VALUES
(1, 'Shanmei Shen', 'Anhui province Xuancheng city', '19309634450', 'ly2006729@qq.com', '$2y$10$yosujBPzgqBtkVMIqJfKbOFYtapu9LywgrjeVfKtBrtq3/pLDkWj2', '2026-05-10 12:56:40', '19309634450'),
(4, 'yuqing zhan', 'ls', '19309637271', 'jp2024213857@qmul.ac.uk', '$2y$10$0jRL7cvUciMPCUJInLGDhuG9U54H8p2Eo621YZD3VV2yLf5ClOUl2', '2026-05-10 17:46:54', '706898'),
(5, 'qjl', 'hn', '15797987271', '123456@qq.com', '$2y$10$kZ8NdhEmpUWIHNRDtJQs4eRkxCjUgeLHlsdumWhUciC2VETT8nJXm', '2026-05-10 18:28:48', 'qjl'),
(6, 'dzr', 'hn', '11111111111', '654321@qq.com', '$2y$10$fsw9gaIMkb0O4EWUsvJ7XuiP3NllEU8AwimMlHS9k/WGVfdXupf.m', '2026-05-11 09:20:44', 'dzr'),
(10, 'ly', 'Anhui province Xuancheng city', '18056307253', 'ly20060729@qq.com', '$2y$10$XzVzPQvDSauR..NGzLIh..GkZtm2mRCWZE58PGtL38QKbGHkOn5ya', '2026-05-11 09:32:09', '18056307253'),
(11, 'liang hongbin', 'Anhui province Xuancheng city', '18056307832', 'lhb2006729@qq.com', '$2y$10$hJyAEjcCxtqIN9DdpzD4wu3ER9OxMMyVmwZw1m.L1w0dW1RLLtawG', '2026-05-11 11:38:00', 'lhb'),
(13, 'lhb', 'Anhui province Xuancheng city', '15105631119', 'lhb20060729@qq.com', '$2y$10$iQ0ODhTo2sCS2TECzaA.uOW6J3KNLwPox7gaAS2z5skri9mujwvlK', '2026-05-11 11:42:33', 'liang hb'),
(14, 'jack', 'hainan', '13979225295', '111@qq.com', '$2y$10$wrKUC2YqrsC47iUyTGh7eexUtiunqQ3nUQAZVjglUgaTEjtlSBFfW', '2026-05-11 11:51:42', '111'),
(17, 'rose', 'hn', '33333333333', '333@qq.com', '$2y$10$hH2n2tth0s4bjLT1syMqBOaYRSd/02B5dJUaygnP/uN1MFR0GCAGe', '2026-05-11 12:22:41', 'rose');

-- --------------------------------------------------------

--
-- 表的结构 `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `color` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seller_username` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `vehicles`
--

INSERT INTO `vehicles` (`id`, `color`, `model`, `year`, `location`, `price`, `image_path`, `created_at`, `seller_username`) VALUES
(1, 'red', 'audi', 2006, 'ls', 20000.00, 'uploads/1778421492_6a008ef4ec42e.jpg', '2026-05-10 13:58:12', NULL),
(2, 'red', 'audi', 2006, 'ls', 20000.00, 'uploads/1778421516_6a008f0c957a8.jpg', '2026-05-10 13:58:36', NULL),
(3, 'red', 'audi', 2006, 'ls', 200000.00, 'uploads/1778437877_6a00cef51b869.jpg', '2026-05-10 18:31:17', 'qjl'),
(4, 'White', 'audi', 2006, 'ls', 200000.00, '', '2026-05-11 09:08:15', NULL),
(5, 'red', 'audi', 2006, 'ls', 20000.00, 'uploads/1778491445_6a01a03503569.jpg', '2026-05-11 09:24:05', 'dzr'),
(6, 'blue', 'audi', 2024, 'beijing', 23000.00, 'uploads/1778493002_6a01a64a2305e.jpg', '2026-05-11 09:50:02', 'dzr'),
(7, 'blue', 'audi', 2024, 'lingshui', 23000.00, 'uploads/1778493175_6a01a6f777b08.webp', '2026-05-11 09:52:55', 'dzr');

--
-- 转储表的索引
--

--
-- 表的索引 `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_username` (`username`);

--
-- 表的索引 `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vehicle_seller` (`seller_username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `sellers`
--
ALTER TABLE `sellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 限制导出的表
--

--
-- 限制表 `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicle_seller` FOREIGN KEY (`seller_username`) REFERENCES `sellers` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
