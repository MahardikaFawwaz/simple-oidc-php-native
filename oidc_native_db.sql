-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 15, 2025 at 06:53 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `oidc_native_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_id` varchar(64) NOT NULL,
  `client_secret` varchar(128) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_name`, `client_id`, `client_secret`, `created_at`) VALUES
(1, 'Aplikasi Keuangan', '93c25cb2c187b52f', '0d649bde170e64cdb22b930ba5e98915', '2025-12-10 02:41:58'),
(2, 'Web', '14c97a0a60581465', '0ad3090ec95053138ee6dcfb75dc2190', '2025-12-11 06:22:38'),
(3, 'UMKM', 'f6db6fd4739a59c9', '1c480f35231c93928896c50bd9d9665f', '2025-12-11 06:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `login_requests`
--

CREATE TABLE `login_requests` (
  `id` int NOT NULL,
  `client_id` varchar(64) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_requests`
--

INSERT INTO `login_requests` (`id`, `client_id`, `status`, `created_at`) VALUES
(1, '93c25cb2c187b52f', 'rejected', '2025-12-11 06:16:09'),
(2, '93c25cb2c187b52f', 'rejected', '2025-12-11 06:18:35'),
(3, '93c25cb2c187b52f', 'approved', '2025-12-11 06:19:01'),
(4, '93c25cb2c187b52f', 'approved', '2025-12-11 06:19:59'),
(5, '93c25cb2c187b52f', 'approved', '2025-12-11 06:21:20'),
(6, '14c97a0a60581465', 'approved', '2025-12-11 06:23:55'),
(7, '93c25cb2c187b52f', 'approved', '2025-12-11 06:28:32'),
(8, '93c25cb2c187b52f', 'approved', '2025-12-11 06:29:11'),
(9, '93c25cb2c187b52f', 'approved', '2025-12-11 06:30:23'),
(10, '93c25cb2c187b52f', 'approved', '2025-12-11 06:35:06'),
(11, '93c25cb2c187b52f', 'approved', '2025-12-11 06:35:17'),
(12, '93c25cb2c187b52f', 'rejected', '2025-12-11 06:35:43'),
(13, '93c25cb2c187b52f', 'approved', '2025-12-11 06:36:02'),
(14, '93c25cb2c187b52f', 'approved', '2025-12-11 06:39:03'),
(15, '93c25cb2c187b52f', 'rejected', '2025-12-11 06:39:39'),
(16, '93c25cb2c187b52f', 'rejected', '2025-12-11 06:39:51'),
(17, '93c25cb2c187b52f', 'approved', '2025-12-11 06:40:06'),
(18, '93c25cb2c187b52f', 'rejected', '2025-12-11 06:45:16'),
(19, '93c25cb2c187b52f', 'approved', '2025-12-11 06:45:35'),
(20, '93c25cb2c187b52f', 'approved', '2025-12-11 06:47:28'),
(21, '93c25cb2c187b52f', 'approved', '2025-12-11 06:55:55'),
(22, '93c25cb2c187b52f', 'approved', '2025-12-11 07:00:12'),
(23, '93c25cb2c187b52f', 'approved', '2025-12-15 06:48:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_id` (`client_id`);

--
-- Indexes for table `login_requests`
--
ALTER TABLE `login_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_requests`
--
ALTER TABLE `login_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
