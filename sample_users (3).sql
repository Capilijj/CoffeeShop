-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:4306
-- Generation Time: Sep 30, 2025 at 04:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sample_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `addon_options`
--

CREATE TABLE `addon_options` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `extra_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addon_options`
--

INSERT INTO `addon_options` (`id`, `name`, `category`, `extra_price`, `is_available`) VALUES
(1, 'Small', 'Size', 0.00, 1),
(2, 'Medium', 'Size', 15.00, 1),
(3, 'Large', 'Size', 30.00, 1),
(4, '100% Sugar', 'Sugar Level', 0.00, 1),
(5, '75% Sugar', 'Sugar Level', 0.00, 1),
(6, '50% Sugar', 'Sugar Level', 0.00, 1),
(7, '25% Sugar', 'Sugar Level', 0.00, 1),
(8, '0% Sugar', 'Sugar Level', 0.00, 1),
(9, 'Oat Milk', 'Milk', 20.00, 1),
(10, 'Extra Shot', 'Shot', 15.00, 1),
(11, 'Hot', 'Temperature', 0.00, 1),
(12, 'Iced', 'Temperature', 5.00, 1),
(13, 'Blended', 'Temperature', 20.00, 1),
(14, 'Soy Milk', 'Milk', 25.00, 1),
(15, 'Creamer', 'Milk', 0.00, 1),
(16, 'Toffee Nut Syrup', 'Syrup', 10.00, 1),
(17, 'Peppermint Syrup', 'Syrup', 10.00, 1),
(18, 'Extra Whipped Cream', 'Topping', 15.00, 1),
(19, 'Chocolate Chips', 'Topping', 20.00, 1),
(20, 'Cinnamon Powder', 'Topping', 5.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menuid` int(11) NOT NULL,
  `menuname` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `imageurl` varchar(255) NOT NULL,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menuid`, `menuname`, `description`, `price`, `imageurl`, `createdat`) VALUES
(1, 'Choco Frappe', '\nA delicious chocolate frappe topped with whipped cream, chocolate syrup, and chocolate.', 55.00, '../ImageMenu/Choco_Frappe.jfif', '2025-09-04 01:12:44'),
(2, 'Choco Tin Coffee', 'An iced mocha with swirls of chocolate syrup is served in a tall glass, surrounded by ice cubes and chocolate chips on a marble surface.', 55.00, '../ImageMenu/Choco_Tin_Coffee.jfif', '2025-09-04 01:16:49'),
(3, 'Macha lia Coffee', 'An iced matcha latte, a vibrant green beverage made from finely ground green tea powder and milk, often served over ice.', 55.00, '../ImageMenu/Macha_lia.jfif', '2025-09-04 01:16:49'),
(4, 'vietnamese iced coffee', 'A strong, sweet coffee brewed with a traditional \"phin\" filter and mixed with sweetened condensed milk, served over ice.', 55.00, '../ImageMenu/vietnamese_iced_coffee.jfif', '2025-09-04 01:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `menuid` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Processing','Cancelled','Payment Verified','In Progress','Completed','Invalid') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_addons`
--

CREATE TABLE `order_addons` (
  `order_addon_id` int(11) NOT NULL,
  `orderid` int(11) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `has_given_onetime_feedback` tinyint(1) NOT NULL DEFAULT 0,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `Email`, `Password`, `contact_no`, `address`, `profile_img`, `verified`, `has_given_onetime_feedback`, `Created_at`, `role`) VALUES
(1, 'James Capili', 'justinecapili92@gmail.com', '$2y$10$ilMHUCIuKyNfPig7oY/hVuYWMADj0DST4VtYnqZkaKi2mO50sG7Iy', '09519181553', 'Duhat Street, Hillside Village, Payatas, 2nd District, Quezon City, Eastern Manila District, Metro Manila, 1119, Philippines', 'uploads/profile_1.jpg', 1, 0, '2025-09-04 03:31:27', 'admin'),
(2, 'Capilijj', 'justincapili20@gmail.com', '$2y$10$X/JNwbOELwP8sn5zP57JPuBqgEHLptLToFnIJn4j6dXvMpSB2Vm6S', '0909', 'Duhat Street, Hillside Village, Payatas, 2nd District, Quezon City, Eastern Manila District, Metro Manila, 1119, Philippines', 'uploads/profile_2.jpg', 1, 1, '2025-09-29 23:52:13', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `user_feedback`
--

CREATE TABLE `user_feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feedback_type` enum('One-time Prompt','Suggestions Page','Order Specific') NOT NULL,
  `feedback_text` text NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addon_options`
--
ALTER TABLE `addon_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menuid`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderid`),
  ADD KEY `fk_userid` (`userid`),
  ADD KEY `fk_orders_menuid` (`menuid`);

--
-- Indexes for table `order_addons`
--
ALTER TABLE `order_addons`
  ADD PRIMARY KEY (`order_addon_id`),
  ADD KEY `orderid` (`orderid`),
  ADD KEY `addon_id` (`addon_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `id_2` (`id`),
  ADD UNIQUE KEY `uq_users_email` (`Email`);

--
-- Indexes for table `user_feedback`
--
ALTER TABLE `user_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addon_options`
--
ALTER TABLE `addon_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menuid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_addons`
--
ALTER TABLE `order_addons`
  MODIFY `order_addon_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_feedback`
--
ALTER TABLE `user_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_menuid` FOREIGN KEY (`menuid`) REFERENCES `menu` (`menuid`);

--
-- Constraints for table `order_addons`
--
ALTER TABLE `order_addons`
  ADD CONSTRAINT `order_addons_ibfk_1` FOREIGN KEY (`orderid`) REFERENCES `orders` (`orderid`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_addons_ibfk_2` FOREIGN KEY (`addon_id`) REFERENCES `addon_options` (`id`);

--
-- Constraints for table `user_feedback`
--
ALTER TABLE `user_feedback`
  ADD CONSTRAINT `user_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
