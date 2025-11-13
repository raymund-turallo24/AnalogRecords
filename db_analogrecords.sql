-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 12:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_analogrecords`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `status` enum('active','inactive') DEFAULT 'active',
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `email`, `password`, `role`, `status`, `date_created`) VALUES
(1, 'elijah15gallardo@gmail.com', '$2y$10$DhzB/tyYktvVZh52hKHaeeDKi6oHGsbZq5Fl36.xV59T5AVtgygZG', 'customer', 'active', '2025-11-10 02:56:37'),
(2, 'elijah@gmail.com', '$2y$10$/0yqzqmcawPNMwQF7II4Cuky8HFy53a2i4CcD/olcJ7kAEO8elRSS', 'customer', 'active', '2025-11-10 03:02:58'),
(3, 'user1@gmail.com', '$2y$10$hVqmkGBbjLY4.c83rsUcceCgUc9nUBLNnBAtHuWv03kqP4nOSQ4UG', 'customer', 'active', '2025-11-10 03:05:16'),
(4, 'cian@gmail.com', '$2y$10$KmnSKRmZKh5HIKKQ0rceAelVhAYDb2uwdGaRk6/N6tzw1Kdf8BUpS', 'customer', 'active', '2025-11-10 03:09:12'),
(5, 'admin@example.com', '$2y$10$wH6KqzW0d1J5P9F3Z5V/cexA9e8Rk4F2bJYgFZ5q0eI7cM8tAq3vO', 'admin', 'active', '2025-11-10 03:24:32'),
(6, 'admin2@example.com', '$2y$10$8BPIPkUUbAqr1tYVrALfVuN2Rl0q4j3zDu3HzwoGRgFPusimWSXZK', 'admin', 'active', '2025-11-10 03:30:35'),
(7, 'eli@gmail.com', '$2y$10$U/Vkxdx0I4KH3WXd9ayxQu/U4.FYZJeWoEcHLjFecj.HJe2F8xRQW', 'customer', 'active', '2025-11-11 18:31:01'),
(8, 'user3@gmail.com', '$2y$10$VhsNHg/AyNiQWDDY6Ysal.bMg5k8867Rem2QrBf1oN7EnlcEqrO1K', 'customer', 'active', '2025-11-11 18:33:48'),
(10, 'user5@gmail.com', '$2y$10$b0bMIqWuZuGpXZZ6tpK5DO5SWjHQPYwNmM9zyQMXSsC5nMq4T6mrO', 'customer', 'active', '2025-11-11 19:04:40'),
(11, 'user10@gmail.com', '$2y$10$Mv31a5E5Ff7QZw6oC.YPOuvYDLFQa3hYy1huMNUkEvTektV7FnCrW', 'customer', 'active', '2025-11-11 19:39:31'),
(12, 'user11@gmail.com', '$2y$10$ChErAAXhCjFNUSrDObcFcO/3wIV2Fri4cvs5LYgA2dy8pVAQlaw.m', 'customer', 'active', '2025-11-11 20:49:26'),
(13, 'stanley@gmail.com', '$2y$10$PvVy/1C34rTsE54HdygpUOLGSAw8edHzKX03uXzbsZeHsKEkIr0by', 'customer', 'active', '2025-11-11 21:02:44'),
(14, 'stan@gmail.com', '$2y$10$Rqd5QA0yooO9aJmrjjgWSO0lMtCr6xS4hy05TXAiMeDLxnE6c0hPC', 'customer', 'active', '2025-11-11 21:05:39'),
(15, 'ray@gmail.com', '$2y$10$j9ZXlBOXZFELvNKMB.8gHO9/ZOUhhiiwNIFuI60QrSA6Tdwdvh5lm', 'customer', 'active', '2025-11-11 21:17:02'),
(16, 'luz@gmail.com', '$2y$10$DIBV8/W05rVsi3aAP/sCd.z3rrT.ZRtXXMCHgzmKHolZymKco62Ia', 'customer', 'active', '2025-11-11 21:25:10'),
(17, 'mary@gmail.com', '$2y$10$L8JXWXdLR1fMMWU6DYG05u2EV.PtiaNOOT./8iIbdDz.K0z4ErQDC', 'customer', 'active', '2025-11-11 21:26:44'),
(18, 'elisha@gmail.com', '$2y$10$t8jhbZzxz.cYqFizQXo5lOBNjfC9IEsFEE1UCpMFBeCY1gtqvpIsW', 'customer', 'active', '2025-11-11 21:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `customer_details`
--

CREATE TABLE `customer_details` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `contact` int(45) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_details`
--

INSERT INTO `customer_details` (`customer_id`, `first_name`, `last_name`, `contact`, `address`, `image`, `date_created`, `account_id`) VALUES
(1, '', '', 0, '', 'img_6910e4e44ca34.jpg', '2025-11-09 20:00:52', 1),
(2, 'elijah', 'gallardo', 2147483647, '313, Saint Jude Street, Holy Family Village', NULL, '2025-11-09 20:03:20', 2),
(3, 'elijah', 'gallardo', 2147483647, '313, Saint Jude Street, Holy Family Village', NULL, '2025-11-09 20:06:23', 3),
(4, 'cian', 'de sagun', 2147483647, '313, pas street', NULL, '2025-11-09 20:09:45', 4),
(5, 'Elijah Neil', 'Gallardo', 12992102, '313, Saint Jude Street, Holy Family Village', 'img_691310733e32c.jpg', '2025-11-11 11:31:15', 7),
(6, '', '', 0, '', 'img_691311e687498.jpg', '2025-11-11 11:37:26', 8),
(7, 'Elijah Neil', 'Gallardo', 2147483647, '313, Saint Jude Street, Holy Family Village', 'img_69131854c56e6.jpg', '2025-11-11 12:04:52', 10),
(8, 'Elijah Neil', 'Gallardo', 2147483647, '313, Saint Jude Street, Holy Family Village', 'img_6913209d3cba3.png', '2025-11-11 12:40:13', 11),
(9, 'ello', 'Lucero', 931, 'Saint Mary Street, HFV', 'GallardoMediumQuiz.png', '2025-11-11 13:50:12', 12),
(10, 'ello', 'Lucero', 931, '313, Saint Jude Street, Holy Family Village', 'GallardoMediumQuiz.png', '2025-11-11 21:02:44', 13),
(11, 'ello', 'Lucero', 931, '313, Saint Jude Street, Holy Family Village', 'GallardoMediumQuiz.png', '2025-11-11 14:03:12', 13),
(12, '', '', NULL, '', NULL, '2025-11-11 21:05:39', 14),
(13, 'Stanley', 'Sarreal', 931311, '313, Saint Jude Street, Holy Family Village', 'img_691334b510c21.jpg', '2025-11-11 14:05:57', 14),
(14, 'ello', 'Lucero', 931, '12', 'WIN_20250804_15_58_39_Pro.jpg', '2025-11-11 21:17:02', 15),
(15, 'ello', 'Lucero', 931, '12', 'WIN_20250804_15_58_39_Pro.jpg', '2025-11-11 14:17:17', 15),
(16, '', '', NULL, '', NULL, '2025-11-11 21:25:10', 16),
(17, 'luz', 'de arao', 11111111, '313, Saint Jude Street, Holy Family Village', 'img_6913394d4e93b.jpg', '2025-11-11 14:25:33', 16),
(18, '', '', NULL, '', NULL, '2025-11-11 21:26:44', 17),
(19, 'mary', 'de arao', 11111111, '313, Saint Jude Street, Holy Family Village', 'img_691339a5ec96d.jpg', '2025-11-11 14:27:01', 17),
(20, 'elisha', 'gallardo', 12345, '313 saint jude street', 'WIN_20250804_15_58_46_Pro.jpg', '2025-11-11 21:28:41', 18);

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `title`, `artist`, `genre`, `price`, `description`, `quantity`) VALUES
(10, 'Lover', 'Taylor', 'Metal', 200.00, 'Should be soty', 100);

-- --------------------------------------------------------

--
-- Table structure for table `item_images`
--

CREATE TABLE `item_images` (
  `image_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_images`
--

INSERT INTO `item_images` (`image_id`, `item_id`, `image`) VALUES
(32, 10, '1762944297_PURCHASE.jpg'),
(33, 10, '1762944297_SYSTEM UI - CARFUN (1).jpg'),
(34, 10, '1762944297_SYSTEM UI - CARFUN.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orderinfo`
--

CREATE TABLE `orderinfo` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `order_status` varchar(45) DEFAULT 'Pending',
  `shipping_address` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderline`
--

CREATE TABLE `orderline` (
  `orderline_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `stock_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customer_details`
--
ALTER TABLE `customer_details`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `fk_customer_account` (`account_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `item_images`
--
ALTER TABLE `item_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `orderinfo`
--
ALTER TABLE `orderinfo`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `orderline`
--
ALTER TABLE `orderline`
  ADD PRIMARY KEY (`orderline_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `stock_ibfk_1` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `customer_details`
--
ALTER TABLE `customer_details`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `item_images`
--
ALTER TABLE `item_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `orderinfo`
--
ALTER TABLE `orderinfo`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderline`
--
ALTER TABLE `orderline`
  MODIFY `orderline_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_details`
--
ALTER TABLE `customer_details`
  ADD CONSTRAINT `fk_customer_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `item_images`
--
ALTER TABLE `item_images`
  ADD CONSTRAINT `item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `orderinfo`
--
ALTER TABLE `orderinfo`
  ADD CONSTRAINT `orderinfo_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_details` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `orderline`
--
ALTER TABLE `orderline`
  ADD CONSTRAINT `orderline_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orderinfo` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orderline_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
