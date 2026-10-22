-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2026 at 11:00 AM
-- Server version: 8.4.11-0ubuntu0.26.04.1
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `user_id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, NULL, 'Mariba Vincent', 'vinniemariba2004@gmail.com', '0712345678', 'None of your B/S', 'Here we go!!', 'unread', '2026-10-05 15:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `impression` text,
  `first_hear` text,
  `missing_features` text,
  `recommend` tinyint DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unread',
  `useful_features` text,
  `ease_of_use` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `impression`, `first_hear`, `missing_features`, `recommend`, `status`, `useful_features`, `ease_of_use`, `created_at`) VALUES
(1, 3, 'I  made this shit brick by brick.', 'My mind', 'Yeah but still working on it', 5, 'read', 'Everything', 'Very easy', '2026-09-27 15:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reservation_id` int NOT NULL,
  `currency` varchar(5) DEFAULT 'KES',
  `method` varchar(20) DEFAULT 'card',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_ref` varchar(100) DEFAULT NULL,
  `payment_meta` text,
  `amount` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `no_adults` int DEFAULT NULL,
  `no_children` int DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `reservation_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int NOT NULL,
  `room_number` int NOT NULL,
  `room_name` varchar(30) NOT NULL,
  `room_type` varchar(30) NOT NULL,
  `room_featured` int NOT NULL,
  `room_price` double(10,3) NOT NULL,
  `room_booked` int DEFAULT '0',
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `room_image` varchar(50) NOT NULL,
  `room_floor` int NOT NULL,
  `room_view` varchar(20) NOT NULL,
  `room_beds` int NOT NULL,
  `bed_type` varchar(30) NOT NULL,
  `room_capacity` int NOT NULL,
  `room_amenities` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `room_name`, `room_type`, `room_featured`, `room_price`, `room_booked`, `check_in_date`, `check_out_date`, `room_image`, `room_floor`, `room_view`, `room_beds`, `bed_type`, `room_capacity`, `room_amenities`) VALUES
(32, 101, 'Standard Single', 'Standard', 0, 2500.000, 0, NULL, NULL, 'room_0199fff562c0a4c7.jpg', 1, 'Garden View', 1, 'Single', 1, 'Wi-Fi, TV, Shower'),
(33, 102, 'Standard Double', 'Standard', 0, 3500.000, 0, NULL, NULL, 'room_2d3a39215e3f2977.jpg', 1, 'Garden View', 1, 'Double', 2, 'Wi-Fi, TV, AC, Shower'),
(34, 103, 'Twin Comfort', 'Standard', 0, 4000.000, 0, NULL, NULL, 'room_9d7b6f3ca33ac380.jpg', 1, 'Courtyard View', 2, 'Single', 2, 'Wi-Fi, TV, Desk'),
(35, 104, 'Deluxe Queen', 'Deluxe', 1, 5500.000, 0, NULL, NULL, 'room_3bec43829ac63c08.jpg', 1, 'Pool View', 1, 'Queen', 2, 'Wi-Fi, TV, AC, Mini Fridge'),
(36, 201, 'Deluxe King', 'Deluxe', 1, 6500.000, 0, NULL, NULL, 'room_6d3af7e2463a03b7.jpg', 2, 'City View', 1, 'King', 2, 'Wi-Fi, TV, AC, Balcony'),
(37, 202, 'Executive Suite', 'Suite', 1, 9000.000, 0, NULL, NULL, 'room_538073d7aea4a4e2.jpg', 2, 'City View', 1, 'King', 2, 'Wi-Fi, TV, AC, Sofa, Mini Fridge'),
(38, 203, 'Family Room', 'Family', 1, 7500.000, 0, NULL, NULL, 'room_854615a50b0e3f2f.jpg', 2, 'Garden View', 3, 'Double + Single', 3, 'Wi-Fi, TV, AC, Wardrobe'),
(39, 204, 'Honeymoon Suite', 'Suite', 1, 12022.000, 0, NULL, NULL, 'room_57ec8bbfe31741d6.jpg', 2, 'Pool View', 1, 'King', 2, 'Wi-Fi, TV, AC, Bathtub, Balcony'),
(40, 301, 'Presidential Suite', 'Suite', 1, 20000.000, 0, NULL, NULL, 'room_efe300057e0cef65.jpg', 3, 'Panoramic City View', 2, 'King', 4, 'Wi-Fi, Smart TV, AC, Jacuzzi, Lounge'),
(41, 302, 'Family Deluxe', 'Family', 1, 10000.000, 0, NULL, NULL, 'room_fda8e207dec205d5.jpg', 3, 'Mountain View', 2, 'Queen', 4, 'Wi-Fi, TV, AC, Mini Fridge, Desk'),
(42, 303, 'Standard Queen', 'Standard', 0, 4500.000, 0, NULL, NULL, 'room_79c693e2041b7069.jpg', 3, 'Garden View', 1, 'Queen', 2, 'Wi-Fi, TV, Shower'),
(43, 304, 'Premium King', 'Premium', 0, 7000.000, 0, NULL, NULL, 'room_bcc6537af5b7838b.jpg', 3, 'City View', 1, 'King', 2, 'Wi-Fi, Smart TV, AC, Mini Fridge'),
(44, 305, 'Deluxe Twin', 'Deluxe', 0, 6000.000, 0, NULL, NULL, 'room_231ac44d22295ac1.jpg', 3, 'Mountain View', 2, 'Single', 2, 'Wi-Fi, TV, AC, Desk'),
(45, 306, 'Business Suite', 'Suite', 0, 10000.000, 0, NULL, NULL, 'room_d07a03e80253b1bb.jpg', 3, 'City View', 1, 'King', 2, 'Wi-Fi, Smart TV, AC, Work Desk, Sofa'),
(46, 307, 'Family Comfort', 'Family', 0, 8500.000, 0, NULL, NULL, 'room_06db73d6c9b2c84c.jpg', 3, 'Garden View', 3, 'Double + Single', 4, 'Wi-Fi, TV, AC, Wardrobe'),
(47, 308, 'Luxury Suite', 'Suite', 0, 14000.000, 0, NULL, NULL, 'room_6d47525d5942f1f8.jpg', 3, 'Pool View', 1, 'King', 2, 'Wi-Fi, Smart TV, AC, Bathtub, Balcony'),
(48, 309, 'Economy Single', 'Economy', 0, 2000.000, 0, NULL, NULL, 'room_f5cf5266f3198754.jpg', 3, 'Courtyard View', 1, 'Single', 1, 'Wi-Fi, TV, Shower, Desk'),
(49, 310, 'Superior Double', 'Superior', 1, 5000.000, 0, NULL, NULL, 'room_1c376c7b2a1497de.jpg', 3, 'City View', 1, 'Double', 2, 'Wi-Fi, TV, AC, Mini Fridge'),
(50, 311, 'Accessible Room', 'Standard', 0, 5000.000, 0, NULL, NULL, 'room_8c6f9ea0d772efff.jpg', 3, 'Garden View', 1, 'Queen', 2, 'Wi-Fi, TV, Accessible Bathroom, Grab Rails'),
(51, 312, 'Royal Suite', 'Suite', 0, 18000.000, 0, NULL, NULL, 'room_dd0509e9a33a9fbb.jpg', 3, 'Panoramic View', 2, 'King', 4, 'Wi-Fi, Smart TV, AC, Lounge, Jacuzzi'),
(52, 313, 'Triple Comfort', 'Family', 0, 7000.000, 0, NULL, NULL, 'room_64a9fe639c872943.jpg', 3, 'Mountain', 3, 'Single', 3, 'Wi-Fi, TV, AC, Wardrobe, Desk'),
(53, 314, 'Premium Family Suite', 'Suite', 1, 15000.000, 0, NULL, NULL, 'room_5371b576f7f7050e.jpg', 3, 'Pool View', 2, 'Queen', 4, 'Wi-Fi, Smart TV, AC, Sofa, Mini Fridge, Balcony');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_fname` varchar(20) NOT NULL,
  `user_lname` varchar(20) NOT NULL,
  `user_verified` int NOT NULL DEFAULT '0',
  `verification_hash` varchar(500) DEFAULT NULL,
  `user_dob` date NOT NULL,
  `user_phone` varchar(15) NOT NULL,
  `user_admin` int NOT NULL DEFAULT '0',
  `user_password` varchar(500) NOT NULL,
  `user_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_image` varchar(20) NOT NULL DEFAULT 'default_user.jpg',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_email`, `user_fname`, `user_lname`, `user_verified`, `verification_hash`, `user_dob`, `user_phone`, `user_admin`, `user_password`, `user_created`, `user_image`, `reset_token`, `reset_expires`) VALUES
(3, 'admin@gmail.com', 'Admin ', 'Account', 1, '5a4b25aaed25c2ee1b74de72dc03c14e', '2000-07-19', '0213123118024', 1, '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '2020-11-22 17:52:46', 'default_user.jpg', NULL, NULL),
(13, 'nancy@gmail.com', 'Nancy', 'K', 1, '', '2004-09-24', '0705681215', 0, '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', '2026-10-11 12:13:17', 'default_user.jpg', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payment_idfk_1` (`reservation_id`),
  ADD KEY `payment_idfk_2` (`user_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_idfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_idfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
