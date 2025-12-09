-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 04:52 AM
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
-- Database: `system`
--

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `purchases` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `purchase_type` enum('product','session','monthly') DEFAULT 'product',
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `plan_type` varchar(20) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `Image` varchar(500) DEFAULT NULL,
  `Product_Name` varchar(255) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `Image`, `Product_Name`, `Price`, `Description`) VALUES
(1, 'https://athlene.com.ph/cdn/shop/files/6_e82a3b4f-4387-4cf0-99b0-4d015640a713.png?v=1697512683', 'ACTIVE Whey Protein', 59.66, 'A premium whey protein powder with unrivaled taste and quality. With only 124 calories per serving, packed with 24g of protein and an outstanding amino acid profile rich in naturally occurring BCAAs and glutamic acid, ACTIVE Whey protein is the perfect protein choice to fuel recovery and lean muscle gains.'),
(2, 'https://athlene.com.ph/cdn/shop/files/OLDPACKAGING_1.png?v=1693878038&width=600', 'ACTIVE Creatine Monohydrate', 11.92, 'A pure, unadulterated form of creatine monohydrate, a supplement that helps muscles produce energy for short-term, high-intensity exercise.'),
(3, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcSEIHEarQ1jOIRQP6kgMPaU5PARTKZ0M7ZcfbfUZ46OTvelCSRdmBZiDyF_NeK20ltxHuj2ekWHoWXs4MTjxW9tGXp1c132Lt87I0dzOQYgv0D5-v8ipnTgNilkXYSTcSApWZgT_YhMFw&usqp=CAc', 'Optimum Nutrition Pre-Workout', 29.66, 'For Performance, Energy and Focus\r\n\r\nGold Standard Pre-Workout combines caffeine with creatine monohydrate and beta-alanine to help you unleash energy, focus, performance and endurance. Whether your goal is reach the pinnacle of your game, crush your next set, or get that last rep, get your energy with the pre-workout from one of the most trusted brands in sports nutrition.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `passcode` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `Email`, `Name`, `passcode`) VALUES
(1, 'gymrat@gmail.com', 'admin', 'admin12345'),
(2, 'jhester@gmail.com', 'jhester', 'jhesty12345'),
(3, 'cedie@gmail.com', 'cedie', 'cedie12345'),
(4, 'marby@gmail.com', 'marby', 'marby12345'),
(6, 'carlo@gmail.com', 'carlo', 'carlo12345');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
