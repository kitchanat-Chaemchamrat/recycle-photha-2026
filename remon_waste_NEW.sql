-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2026 at 08:16 AM
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
-- Database: `remon_waste`
--

-- --------------------------------------------------------

--
-- Table structure for table `dividend_details`
--

CREATE TABLE `dividend_details` (
  `id` int(11) NOT NULL,
  `dividend_period_id` int(11) NOT NULL,
  `soi_id` int(11) NOT NULL,
  `avg_score` decimal(10,2) NOT NULL,
  `recycle_weight` decimal(10,2) NOT NULL,
  `weighted_score` decimal(12,2) NOT NULL,
  `ratio_percent` decimal(5,2) NOT NULL,
  `dividend_amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dividend_details`
--

INSERT INTO `dividend_details` (`id`, `dividend_period_id`, `soi_id`, `avg_score`, `recycle_weight`, `weighted_score`, `ratio_percent`, `dividend_amount`) VALUES
(1, 1, 1, 23.50, 5.00, 28.50, 43.51, 31.19),
(2, 1, 2, 21.50, 6.00, 27.50, 56.49, 40.49);

-- --------------------------------------------------------

--
-- Table structure for table `dividend_periods`
--

CREATE TABLE `dividend_periods` (
  `id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_income` decimal(12,2) NOT NULL,
  `community_fund` decimal(12,2) NOT NULL,
  `worker_fund` decimal(12,2) NOT NULL,
  `dividend_pool` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dividend_periods`
--

INSERT INTO `dividend_periods` (`id`, `month`, `year`, `total_income`, `community_fund`, `worker_fund`, `dividend_pool`) VALUES
(1, 6, 2026, 89.60, 8.96, 8.96, 71.68);

-- --------------------------------------------------------

--
-- Table structure for table `houses`
--

CREATE TABLE `houses` (
  `id` int(11) NOT NULL,
  `house_no` varchar(20) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `soi_id` int(11) NOT NULL,
  `member_count` int(11) DEFAULT 1,
  `qr_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `houses`
--

INSERT INTO `houses` (`id`, `house_no`, `owner_name`, `phone`, `soi_id`, `member_count`, `qr_code`) VALUES
(1, '101/1', 'สมชาย ใจดี', '0812345678', 1, 3, 'QR_101_1'),
(2, '101/2', 'สมหญิง รักสะอาด', '0898765432', 2, 4, 'QR_101_2');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `house_id`, `first_name`, `last_name`, `phone`) VALUES
(1, 1, 'สมเกียรติ', 'ใจดี', '0811111111'),
(2, 1, 'สมศรี', 'ใจดี', '0822222222');

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('before','after') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `photos`
--

INSERT INTO `photos` (`id`, `evaluation_id`, `image_path`, `image_type`) VALUES
(1, 1, 'uploads/placeholder_before.jpg', 'before'),
(2, 1, 'uploads/placeholder_after.jpg', 'after');

-- --------------------------------------------------------

--
-- Table structure for table `recycle_sales`
--

CREATE TABLE `recycle_sales` (
  `id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `waste_type` varchar(50) NOT NULL,
  `weight_kg` decimal(10,2) NOT NULL,
  `price_per_kg` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycle_sales`
--

INSERT INTO `recycle_sales` (`id`, `sale_date`, `waste_type`, `weight_kg`, `price_per_kg`, `total_amount`) VALUES
(1, '2026-06-30', 'พลาสติก', 4.00, 10.00, 40.00),
(2, '2026-06-30', 'กระดาษ', 3.20, 5.00, 16.00),
(3, '2026-06-30', 'แก้ว', 1.80, 2.00, 3.60),
(4, '2026-06-30', 'โลหะ', 2.00, 15.00, 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `sois`
--

CREATE TABLE `sois` (
  `id` int(11) NOT NULL,
  `soi_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sois`
--

INSERT INTO `sois` (`id`, `soi_name`, `description`) VALUES
(1, 'ซอย 1', 'โซนหน้าหมู่บ้าน'),
(2, 'ซอย 2', 'โซนกลางหมู่บ้าน');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','collector','auditor','member') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '2026-07-08 05:27:38'),
(2, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-07-08 05:27:38'),
(3, 'collector1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector', '2026-07-08 05:27:38'),
(4, 'auditor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auditor', '2026-07-08 05:27:38');

-- --------------------------------------------------------

--
-- Table structure for table `waste_evaluations`
--

CREATE TABLE `waste_evaluations` (
  `id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `evaluation_date` date NOT NULL,
  `food_score` int(11) DEFAULT 0,
  `plastic_score` int(11) DEFAULT 0,
  `paper_score` int(11) DEFAULT 0,
  `glass_score` int(11) DEFAULT 0,
  `metal_score` int(11) DEFAULT 0,
  `contamination` tinyint(1) DEFAULT 0,
  `total_score` int(11) DEFAULT 0,
  `evaluator_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waste_evaluations`
--

INSERT INTO `waste_evaluations` (`id`, `house_id`, `evaluation_date`, `food_score`, `plastic_score`, `paper_score`, `glass_score`, `metal_score`, `contamination`, `total_score`, `evaluator_id`) VALUES
(1, 1, '2026-06-01', 5, 5, 4, 5, 5, 0, 24, 4),
(2, 1, '2026-06-15', 5, 4, 5, 5, 4, 0, 23, 4),
(3, 2, '2026-06-01', 4, 3, 4, 4, 3, 1, 18, 4),
(4, 2, '2026-06-15', 5, 5, 5, 5, 5, 0, 25, 4);

-- --------------------------------------------------------

--
-- Table structure for table `waste_weights`
--

CREATE TABLE `waste_weights` (
  `id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `plastic_kg` decimal(10,2) DEFAULT 0.00,
  `paper_kg` decimal(10,2) DEFAULT 0.00,
  `glass_kg` decimal(10,2) DEFAULT 0.00,
  `metal_kg` decimal(10,2) DEFAULT 0.00,
  `total_kg` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waste_weights`
--

INSERT INTO `waste_weights` (`id`, `house_id`, `record_date`, `plastic_kg`, `paper_kg`, `glass_kg`, `metal_kg`, `total_kg`) VALUES
(1, 1, '2026-06-25', 2.50, 1.20, 0.80, 0.50, 5.00),
(2, 2, '2026-06-25', 1.50, 2.00, 1.00, 1.50, 6.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dividend_details`
--
ALTER TABLE `dividend_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dividend_period_id` (`dividend_period_id`),
  ADD KEY `soi_id` (`soi_id`);

--
-- Indexes for table `dividend_periods`
--
ALTER TABLE `dividend_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `houses`
--
ALTER TABLE `houses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `soi_id` (`soi_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `house_id` (`house_id`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`);

--
-- Indexes for table `recycle_sales`
--
ALTER TABLE `recycle_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sois`
--
ALTER TABLE `sois`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `waste_evaluations`
--
ALTER TABLE `waste_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `house_id` (`house_id`),
  ADD KEY `evaluator_id` (`evaluator_id`);

--
-- Indexes for table `waste_weights`
--
ALTER TABLE `waste_weights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `house_id` (`house_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dividend_details`
--
ALTER TABLE `dividend_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dividend_periods`
--
ALTER TABLE `dividend_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `houses`
--
ALTER TABLE `houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recycle_sales`
--
ALTER TABLE `recycle_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sois`
--
ALTER TABLE `sois`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `waste_evaluations`
--
ALTER TABLE `waste_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `waste_weights`
--
ALTER TABLE `waste_weights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dividend_details`
--
ALTER TABLE `dividend_details`
  ADD CONSTRAINT `dd_period_fk` FOREIGN KEY (`dividend_period_id`) REFERENCES `dividend_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dd_soi_fk` FOREIGN KEY (`soi_id`) REFERENCES `sois` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `houses`
--
ALTER TABLE `houses`
  ADD CONSTRAINT `houses_ibfk_1` FOREIGN KEY (`soi_id`) REFERENCES `sois` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_eval_fk` FOREIGN KEY (`evaluation_id`) REFERENCES `waste_evaluations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waste_evaluations`
--
ALTER TABLE `waste_evaluations`
  ADD CONSTRAINT `weval_evaluator_fk` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `weval_house_fk` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waste_weights`
--
ALTER TABLE `waste_weights`
  ADD CONSTRAINT `wweight_house_fk` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
