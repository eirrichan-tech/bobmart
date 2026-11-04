-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2026 at 05:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bobmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin User', 'admin@bobmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-10-27 03:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `series_name` varchar(100) NOT NULL,
  `rarity` varchar(50) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `img_path` varchar(255) DEFAULT 'images/default.jpg',
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `boxes_per_case` int(11) NOT NULL DEFAULT 12
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `series_name`, `rarity`, `cost_price`, `sell_price`, `img_path`, `status`, `boxes_per_case`) VALUES
(1, 'Hirono: The Other One', 'Hirono Series', 'Regular', 337.50, 450.00, 'uploads/items/b1.png', 'Active', 12),
(2, 'Hirono: Shelter', 'Hirono Series', 'Regular', 337.50, 450.00, 'uploads/items/b2.png', 'Active', 12),
(3, 'Hirono: Little Mischief', 'Hirono Series', 'Regular', 337.50, 450.00, 'uploads/items/b3.png', 'Active', 12),
(4, 'Hirono: Le Petit Prince', 'Hirono Series', 'Regular', 337.50, 450.00, 'uploads/items/b4.png', 'Active', 12),
(5, 'Hirono: Mime', 'Hirono Series', 'Regular', 337.50, 450.00, 'uploads/items/b5.png', 'Active', 12),
(6, 'Hirono: Echo', 'Hirono Series', 'Regular', 337.50, 450.00, 'uploads/items/b6.png', 'Active', 12),
(7, 'Skullpanda: Everyday Wonderland', 'Skullpanda Series', 'Regular', 337.50, 450.00, 'uploads/items/b7.png', 'Active', 12),
(8, 'Skullpanda: You Found Me', 'Skullpanda Series', 'Regular', 337.50, 450.00, 'uploads/items/b8.png', 'Active', 12),
(9, 'Skullpanda: Xmas', 'Skullpanda Series', 'Regular', 337.50, 450.00, 'uploads/items/b9.png', 'Active', 12),
(10, 'Skullpanda: The Feast Begin', 'Skullpanda Series', 'Regular', 337.50, 450.00, 'uploads/items/b10.png', 'Active', 12),
(11, 'Skullpanda: Image of Reality', 'Skullpanda Series', 'Regular', 337.50, 450.00, 'uploads/items/b11.png', 'Active', 12),
(12, 'Skullpanda: Winter Symphony', 'Skullpanda Series', 'Regular', 337.50, 450.00, 'uploads/items/b12.png', 'Active', 12),
(13, 'Limited Fuggy 13', 'Limited Fuggy', 'Limited', 450.00, 600.00, 'uploads/items/b13.png', 'Active', 12),
(14, 'Limited Fuggy 14', 'Limited Fuggy', 'Limited', 450.00, 600.00, 'uploads/items/b14.png', 'Active', 12),
(15, 'Limited Fuggy 15', 'Limited Fuggy', 'Limited', 450.00, 600.00, 'uploads/items/b15.png', 'Active', 12),
(16, 'Limited Fuggy 16', 'Limited Fuggy', 'Limited', 450.00, 600.00, 'uploads/items/b16.png', 'Active', 12),
(17, 'Limited Fuggy 17', 'Limited Fuggy', 'Limited', 450.00, 600.00, 'uploads/items/b17.png', 'Active', 12),
(18, 'Limited Fuggy 18', 'Limited Fuggy', 'Limited', 450.00, 600.00, 'uploads/items/b18.png', 'Active', 12),
(19, 'Mini Fuggy 19', 'Mini Fuggy', 'Mini', 225.00, 300.00, 'uploads/items/b19.png', 'Active', 12),
(20, 'Mini Fuggy 20', 'Mini Fuggy', 'Mini', 225.00, 300.00, 'uploads/items/b20.png', 'Active', 12),
(21, 'Mini Fuggy 21', 'Mini Fuggy', 'Mini', 225.00, 300.00, 'uploads/items/b21.png', 'Active', 12),
(22, 'Fuggy 22', 'Fuggy', 'Regular', 375.00, 500.00, 'uploads/items/b22.png', 'Active', 12),
(23, 'Fuggy 23', 'Fuggy', 'Regular', 375.00, 500.00, 'uploads/items/b23.png', 'Active', 12),
(25, 'Fuggy 24', 'Fuggy', 'Regular', 375.00, 500.00, 'uploads/items/b25.png', 'Active', 12),
(26, 'Lois', 'Band', 'Secret', 450.00, 600.00, 'images/default.jpg', 'Active', 12);

-- --------------------------------------------------------

--
-- Table structure for table `item_images`
--

CREATE TABLE `item_images` (
  `image_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `img_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_images`
--

INSERT INTO `item_images` (`image_id`, `item_id`, `img_path`) VALUES
(1, 26, 'uploads/items/6ae99b58f0ef11.22643133.png'),
(2, 1, 'uploads/items/6ae9a0ecaecaf1.88594480.png'),
(3, 1, 'uploads/items/6ae9a0ecaf3c81.53426351.png'),
(4, 1, 'uploads/items/6ae9a0ecafa780.93205841.png');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `status`, `created_at`, `shipping_address`) VALUES
(1, 1, 600.00, 'Shipped', '2026-10-27 03:29:47', ''),
(2, 4, 450.00, 'Pending', '2026-11-03 03:20:55', '22A Sampaloc St., Purok 4, Lower Bicutan, Taguig City'),
(3, 4, 1200.00, 'Pending', '2026-11-03 06:07:42', '22A sta ana'),
(4, 11, 450.00, 'Delivered', '2026-11-03 09:03:11', '22A sta ana st'),
(5, 11, 600.00, 'Pending', '2026-11-04 02:16:39', '22A Sampaloc St., Purok 4, Lower Bicutan, Taguig City');

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_details_view`
-- (See below for the actual view)
--
CREATE TABLE `order_details_view` (
`order_id` int(11)
,`user_id` int(11)
,`customer_name` varchar(255)
,`customer_email` varchar(255)
,`total_amount` decimal(10,2)
,`status` varchar(50)
,`created_at` timestamp
,`shipping_address` varchar(255)
,`item_id` int(11)
,`item_name` varchar(255)
,`quantity` int(11)
,`price` decimal(10,2)
,`subtotal` decimal(20,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 600.00),
(2, 2, 12, 1, 450.00),
(3, 3, 15, 2, 600.00),
(4, 4, 1, 1, 450.00),
(5, 5, 26, 1, 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `item_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 11, 5, 'SO CUUUUUUTTTEEEEE I LOVE ITTTT', '2026-11-03 09:06:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `stock_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `stock_cases` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`stock_id`, `item_id`, `quantity`, `stock_cases`) VALUES
(1, 1, 3, 1),
(2, 2, 34, 3),
(3, 3, 38, 4),
(4, 4, 49, 5),
(5, 5, 37, 3),
(7, 7, 100, 100),
(9, 6, 20, 2),
(10, 8, 20, 2),
(11, 9, 20, 2),
(12, 10, 20, 2),
(13, 11, 20, 2),
(14, 12, 19, 2),
(15, 13, 20, 2),
(16, 14, 20, 2),
(17, 15, 18, 2),
(18, 16, 20, 2),
(19, 17, 20, 2),
(20, 18, 20, 2),
(21, 19, 20, 2),
(22, 20, 20, 2),
(23, 21, 20, 2),
(24, 22, 20, 2),
(25, 23, 20, 2),
(26, 25, 20, 2),
(27, 26, 111, 99);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'customer',
  `profile_img` varchar(255) DEFAULT 'images/default_user.jpg',
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `address`, `password`, `role`, `profile_img`, `status`, `created_at`) VALUES
(1, 'Admin User', 'admin@bobmart.com', '', '$2y$12$h2hG5Y5UL0dHm1SiIbgm7eGfcGWMsa6JuuKmkxGyAWWhfUnb7WRjC', 'admin', 'images/default_user.jpg', 'active', '2026-10-27 03:11:49'),
(2, 'mark', 'lloren@yehey.com', '', '$2y$12$j2FTo0hbbuUuEvyvT6ND3.0wXWdsWyF3YElK5ZO26rQjtcAc7KVHa', 'customer', 'images/default_user.jpg', 'active', '2026-10-27 03:17:54'),
(3, 'terd', 'terd@yehey.com', '', '$2y$12$bfE9BWLtA3fSwZ.WpcqZneUhODQz.QrdQstTsWjeYjK1Y538IWiQK', 'customer', 'images/default_user.jpg', 'deactivated', '2026-10-27 03:24:53'),
(4, 'Eirrich Leonardo', 'eri@yehey.com', '', '$2y$12$1MC9wkoNPQO.S247kJcBbemLjLy2ULudniXI8xczBbF.528XuncWi', 'customer', 'images/default_user.jpg', 'active', '2026-11-02 15:36:17'),
(5, 'Eri', 'eirrichan@yehey.com', '', '$2y$12$IPu/onGy4T04aK.kmZkwgez2GjDVq13aR8TJuZnBieWPJmDPBnU1S', 'customer', 'uploads/users/default.png', 'active', '2026-11-02 16:36:53'),
(6, 'Eri', 'eirrich@yehey.com', '', '$2y$12$osfcuNs1wmrvXth2Zaq8/eHccJ59INPrwMAdJsUVoNOZduKbCYSv.', 'customer', 'uploads/users/6ae8bdbfd215a7.44016380.jpg', 'active', '2026-11-02 16:43:03'),
(7, 'System Admin', 'admin2@bobmart.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'images/default_user.jpg', 'active', '2026-11-03 06:20:08'),
(9, 'System Admin', 'admin3@bobmart.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'images/default_user.jpg', 'active', '2026-11-03 06:22:58'),
(10, 'System Admin', 'admin4@bobmart.com', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'images/default_user.jpg', 'active', '2026-11-03 06:24:10'),
(11, 'Andrea', 'sanestebans@yehey.com', '', '$2y$12$UyjFdc7J6FOM2.C8.ukZ5.omdmJ45hntwsePkE5sMAgiKn50XoJgu', 'customer', 'uploads/users/default.png', 'active', '2026-11-03 08:47:34'),
(12, 'Eri', 'eirriches@yehey.com', '22A Sampaloc St., Purok 4, Lower Bicutan, Taguig City', '$2y$12$50yAka2gR1yVtRa.fkDiB.kNbR0L43QyYUmXI73d3VVsgAKd3RNzO', 'customer', 'uploads/users/default.png', 'active', '2026-11-04 02:37:35');

-- --------------------------------------------------------

--
-- Structure for view `order_details_view`
--
DROP TABLE IF EXISTS `order_details_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_details_view`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`user_id` AS `user_id`, `u`.`name` AS `customer_name`, `u`.`email` AS `customer_email`, `o`.`total_amount` AS `total_amount`, `o`.`status` AS `status`, `o`.`created_at` AS `created_at`, `o`.`shipping_address` AS `shipping_address`, `oi`.`item_id` AS `item_id`, `i`.`name` AS `item_name`, `oi`.`quantity` AS `quantity`, `oi`.`price` AS `price`, `oi`.`quantity`* `oi`.`price` AS `subtotal` FROM (((`orders` `o` join `users` `u` on(`u`.`id` = `o`.`user_id`)) join `order_items` `oi` on(`oi`.`order_id` = `o`.`order_id`)) join `items` `i` on(`i`.`id` = `oi`.`item_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_images`
--
ALTER TABLE `item_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `item_images`
--
ALTER TABLE `item_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item_images`
--
ALTER TABLE `item_images`
  ADD CONSTRAINT `item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
