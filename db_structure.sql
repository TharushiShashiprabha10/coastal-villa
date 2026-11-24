-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 06:45 AM
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
-- Database: `coastal_villa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guests_count` int(11) NOT NULL DEFAULT 1,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `booking_status` enum('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `guests_count`, `check_in`, `check_out`, `total_amount`, `booking_status`, `booking_date`) VALUES
(1, 2, 1, 2, '2026-12-10', '2026-12-13', 73500.00, 'Confirmed', '2026-11-24 05:41:13'),
(2, 3, 2, 2, '2026-12-15', '2026-12-17', 33000.00, 'Pending', '2026-11-24 05:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(120) NOT NULL,
  `room_type` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `facilities` varchar(255) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `max_guests` int(11) NOT NULL,
  `room_status` enum('Available','Inactive') NOT NULL DEFAULT 'Available',
  `image_url` varchar(255) DEFAULT 'assets/images/room-default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `room_type`, `description`, `facilities`, `price_per_night`, `max_guests`, `room_status`, `image_url`, `created_at`) VALUES
(1, 'Coral Breeze Suite', 'Ocean View Suite', 'Spacious suite overlooking the Indian Ocean with a private sit-out balcony.', 'King Bed, AC, Free Wi-Fi, Private Balcony, Sea View', 24500.00, 3, 'Available', 'assets/images/coral-suite.jpg', '2026-11-24 05:41:13'),
(2, 'Palm Garden Deluxe', 'Deluxe Room', 'Calm tropical garden-facing room just steps away from the sandy shoreline.', 'Queen Bed, AC, Free Wi-Fi, Hot Water, Garden View', 16500.00, 2, 'Available', 'assets/images/palm-deluxe.jpg', '2026-11-24 05:41:13'),
(3, 'Sunset Family Villa Room', 'Family Room', 'Ideal for families or small groups seeking comfort near surfing & beach spots.', '2 Double Beds, AC, Free Wi-Fi, Mini Fridge, Balcony', 32000.00, 4, 'Available', 'assets/images/family-room.jpg', '2026-11-24 05:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guest','admin') NOT NULL DEFAULT 'guest',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `contact_number`, `password`, `role`, `created_at`) VALUES
(1, 'Villa Admin', 'admin@serenecoastvilla.lk', '0771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-11-24 05:41:13'),
(2, 'Nethmi Perera', 'nethmi.guest@example.com', '0712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest', '2026-11-24 05:41:13'),
(3, 'Kavindu Silva', 'kavindu.guest@example.com', '0763456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest', '2026-11-24 05:41:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_bookings_user` (`user_id`),
  ADD KEY `fk_bookings_room` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
