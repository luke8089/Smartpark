-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2025 at 03:41 PM
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
-- Database: `smartpark`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') NOT NULL DEFAULT 'new',
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equity_payments`
--

CREATE TABLE `equity_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(32) DEFAULT 'pending',
  `transaction_ref` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equity_payments`
--

INSERT INTO `equity_payments` (`id`, `user_id`, `reservation_id`, `account_number`, `account_name`, `amount`, `status`, `transaction_ref`, `created_at`, `updated_at`) VALUES
(4, 2, NULL, '4536288932', 'edwin', 4.00, 'completed', 'EQ18F84F76E738', '2025-07-26 06:28:45', '2025-07-26 06:55:15'),
(6, 4, 50, '12343', 'edwin', 4.00, 'pending', 'EQE4F64296F56C', '2025-07-26 08:51:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(10, 2, 3, 'hi', 'hi', 0, '2025-07-26 13:31:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_payments`
--

CREATE TABLE `mpesa_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(32) DEFAULT 'pending',
  `mpesa_ref` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mpesa_payments`
--

INSERT INTO `mpesa_payments` (`id`, `user_id`, `reservation_id`, `phone`, `amount`, `status`, `mpesa_ref`, `created_at`, `updated_at`) VALUES
(43, 3, 48, '254746075436', 484.00, 'completed', '52f3-4464-b75a-b1bd08f293125346', '2025-07-26 08:23:29', '2025-07-26 08:24:34'),
(45, 4, 49, '254746075436', 4.00, 'pending', '52f3-4464-b75a-b1bd08f293125572', '2025-07-26 08:42:20', '2025-07-26 08:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_campaigns`
--

CREATE TABLE `newsletter_campaigns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `status` enum('draft','scheduled','sent','cancelled') NOT NULL DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_campaigns`
--

INSERT INTO `newsletter_campaigns` (`id`, `name`, `subject`, `content`, `status`, `scheduled_at`, `sent_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Welcome Campaign', 'Welcome to SmartPark!', '<h1>Welcome to SmartPark</h1><p>Thank you for subscribing to our newsletter...</p>', 'draft', NULL, NULL, 2, '2025-07-26 12:49:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_email_logs`
--

CREATE TABLE `newsletter_email_logs` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','delivered','opened','clicked','bounced','failed') NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `bounce_reason` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','unsubscribed') NOT NULL DEFAULT 'active',
  `subscription_type` enum('general','promotions','updates','all') NOT NULL DEFAULT 'general',
  `source` varchar(100) DEFAULT 'website',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `last_email_sent` timestamp NULL DEFAULT NULL,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `unsubscribe_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `first_name`, `last_name`, `phone`, `user_id`, `status`, `subscription_type`, `source`, `ip_address`, `user_agent`, `created_at`, `updated_at`, `last_email_sent`, `unsubscribed_at`, `unsubscribe_reason`) VALUES
(4, 'edwinluke1999@gmail.com', NULL, NULL, NULL, NULL, 'active', 'general', 'website', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 13:04:06', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parking_spots`
--

CREATE TABLE `parking_spots` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `features` varchar(255) DEFAULT NULL,
  `price_hourly` decimal(6,2) NOT NULL,
  `price_daily` decimal(6,2) DEFAULT NULL,
  `price_weekly` decimal(6,2) DEFAULT NULL,
  `available_spots` int(11) NOT NULL,
  `operating_hours` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_spots`
--

INSERT INTO `parking_spots` (`id`, `name`, `location`, `features`, `price_hourly`, `price_daily`, `price_weekly`, `available_spots`, `operating_hours`, `latitude`, `longitude`, `image_url`) VALUES
(4, 'Sarit Center Parking', 'Sarit Centre, Westlands, Nairobi', 'Covered,Security,EV', 2.00, 12.00, 50.00, 10, '6AM - 11PM', -1.2649000, 36.8022000, 'assets/images/spot_1753447333_4040.jpg'),
(6, 'The Hub Karen Parking', 'The Hub Karen, Dagoretti Rd, Nairobi', 'Covered,EV,Handicap', 3.00, 18.00, 70.00, 50, '7AM - 10PM', -1.3292000, 36.7209000, 'assets/images/spot_1753474658_5326.jpg'),
(7, 'Village Market Parking', 'Village Market, Limuru Rd, Nairobi', 'Covered,Security,Handicap', 2.00, 13.00, 55.00, 35, '6AM - 11PM', -1.2195000, 36.8008000, 'https://public.readdy.ai/ai/img_res/df41e2f7c752e8d6e1bcb9df009097ae.jpg'),
(8, 'Two Rivers Mall Parking', 'Two Rivers Mall, Runda, Nairobi', 'Covered,Security,EV,Handicap', 2.50, 16.00, 65.00, 60, '6AM - 11PM', -1.1936000, 36.8227000, 'https://public.readdy.ai/ai/img_res/df41e2f7c752e8d6e1bcb9df009097ae.jpg'),
(11, 'test', 'nairobi westlands', 'test', 1.00, 2.00, 3.00, 13, '6', 999.9999999, 999.9999999, 'assets/images/spot_1753503650_6422.jpg'),
(12, 'User', 'test', 'features', 1.00, 2.00, 3.00, 24, '4', NULL, NULL, 'assets/images/spot_1753503740_9730.jpg'),
(13, 'tenant', 'nairobi westlands', 'features', 23.00, 33.00, 343.00, 24, '4', NULL, NULL, 'assets/images/spot_1753503975_8463.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `spot_number` int(11) DEFAULT NULL,
  `license_plate` varchar(50) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `vehicle_model` varchar(100) NOT NULL,
  `entry_time` datetime NOT NULL,
  `exit_time` datetime NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `booking_ref` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `spot_id`, `spot_number`, `license_plate`, `vehicle_type`, `vehicle_model`, `entry_time`, `exit_time`, `fee`, `payment_method`, `booking_ref`, `created_at`) VALUES
(48, 3, 4, 7, 'KBS234 KR', 'suv', 'kenya', '2025-07-26 10:22:00', '2025-08-05 12:22:00', 484.00, '0', 'BP241EAC16E', '2025-07-26 08:23:12'),
(49, 4, 4, 9, 'KBS234 KR', 'suv', 'kenya', '2025-07-26 10:41:00', '2025-07-26 12:41:00', 4.00, '0', 'BP09E242B2B', '2025-07-26 08:42:00'),
(50, 4, 4, 3, 'KBS234 KR', 'sedan', 'kenya', '2025-07-26 10:51:00', '2025-07-26 12:51:00', 4.00, '0', 'BPD453EAB47', '2025-07-26 08:51:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `profile_image`, `created_at`) VALUES
(2, 'admin', 'admin@gmail.com', '$2y$10$rBvtxs6uGYeIA5Pf6j0JceCGAPR17X0c0AWaXtLxnB5P/x1ny4MVu', 'admin', 'user/uploads/profile_2_1753531120.jpg', '2025-07-24 08:32:45'),
(3, 'User', 'user@gmail.com', '$2y$10$lU2gYfsplx4Jps5H9MZc8OKqdSb1QAao80xTUSUUSHfW/U17Eixbi', 'user', 'uploads/profile_3_1753527562.jpg', '2025-07-26 04:56:03'),
(4, 'User2', 'user2@gmail.com', '$2y$10$ZTtqxpz0HZvEG3Dx83ea4u1d7VJYrhwk082SAcueyAIxXmml1zL9.', 'user', 'uploads/profile_4_1753525301.jpg', '2025-07-26 08:41:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `equity_payments`
--
ALTER TABLE `equity_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `scheduled_at` (`scheduled_at`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `newsletter_email_logs`
--
ALTER TABLE `newsletter_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriber_id` (`subscriber_id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `status` (`status`),
  ADD KEY `sent_at` (`sent_at`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `spot_id` (`spot_id`);

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
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equity_payments`
--
ALTER TABLE `equity_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `newsletter_email_logs`
--
ALTER TABLE `newsletter_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parking_spots`
--
ALTER TABLE `parking_spots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `equity_payments`
--
ALTER TABLE `equity_payments`
  ADD CONSTRAINT `equity_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equity_payments_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD CONSTRAINT `mpesa_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mpesa_payments_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD CONSTRAINT `newsletter_campaigns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `newsletter_email_logs`
--
ALTER TABLE `newsletter_email_logs`
  ADD CONSTRAINT `newsletter_email_logs_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `newsletter_email_logs_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `newsletter_campaigns` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD CONSTRAINT `newsletter_subscribers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
