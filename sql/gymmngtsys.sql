-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2024 at 05:19 PM
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
-- Database: `gymmngtsys`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent') DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `category`, `date`, `image_path`, `last_modified`) VALUES
(15, 'dsdfs', 'dafg', 'Strength and Conditioning', '2024-11-09', 'uploads/6733a25c9e86b-gym7.jpg', '2024-11-14 19:39:35'),
(16, 'cz', 'zvcx', 'Cardio Workouts', '2024-11-15', 'uploads/6733a23d0704e-gym6.jpg', '2024-11-12 18:45:17'),
(17, 'dc', 'sf', 'Mind and Body Classes', '2024-11-08', 'uploads/6733a1aba2ed0-gym5.jpg', '2024-11-12 18:42:51'),
(18, 'dsds', 'ads', 'Functional Training', '2024-11-22', 'uploads/6733a147706c0-gym4.jpg', '2024-11-12 18:41:11'),
(20, 'blk', 'nkj', 'Martial Arts and Self-Defense', '2024-11-13', 'uploads/6733a1198ae25-gym3.jpg', '2024-11-12 18:40:25'),
(21, 'ip', 'ouo', 'Water-Based Fitness', '2024-11-15', 'uploads/673398ddea486-gym2.jpg', '2024-11-12 18:05:17'),
(22, 'jio', 'hi', 'Group Cycling', '2024-11-07', 'uploads/6733981dbe34c-gym1.png', '2024-11-12 18:02:05');

-- --------------------------------------------------------

--
-- Table structure for table `diet_plans`
--

CREATE TABLE `diet_plans` (
  `plan_id` int(11) NOT NULL,
  `fitness_goal` varchar(50) NOT NULL,
  `diet_preference` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diet_plan_meals`
--

CREATE TABLE `diet_plan_meals` (
  `meal_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `day` varchar(20) NOT NULL,
  `breakfast` varchar(100) DEFAULT NULL,
  `lunch` varchar(100) DEFAULT NULL,
  `dinner` varchar(100) DEFAULT NULL,
  `snacks` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `usage_status` varchar(20) DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `available_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `usage_status`, `next_maintenance`, `quantity`, `available_quantity`) VALUES
(25, 'drum', 'available', '2024-11-02', 18, 0),
(26, 'stretch bands', 'available', '2024-11-03', 7, 0),
(27, 'runners', 'available', '2024-11-03', 8, 0),
(28, 'bells', 'available', '2024-11-14', 3, 0),
(29, 'test', 'available', '2024-11-21', 6, 0),
(31, 'equi', 'available', '2024-11-15', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `plan` varchar(50) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `expiry_date` varchar(5) NOT NULL,
  `cvv` varchar(3) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `reserved_from` datetime NOT NULL,
  `reserved_to` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `bmi` decimal(5,2) GENERATED ALWAYS AS (`weight` / (`height` / 100 * `height` / 100)) STORED,
  `phone` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_image`, `age`, `weight`, `height`, `phone`) VALUES
(7, 'admin', 'admin@gmail.com', '$2a$12$ABdBpySbff6QCbX1cwz39OftAoimAIGBzxT9r71BHRdP8KSE5zEQq', 'admin/uploads/profile/1150589.jpg', 0, 0.00, 0.00, ''),
(21, 'test', 'test@gmail.com', '$2y$10$G7CHvhwrrKz5U5Qm1gzq4eDrbZUgzHkAgH5FHiZl48iWfyID35T22', NULL, 9, 5.00, 6.00, ''),
(22, 'test1', 'test1@gmail.com', '$2y$10$ufgW1pGOD2ciFc4IzgMtB.Mw471vEtU7gYopkZkQ5b6DQYRhbm2yC', NULL, 5, 5.00, 6.00, '+94712456785');

-- --------------------------------------------------------

--
-- Table structure for table `user_reservations`
--

CREATE TABLE `user_reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `reserved_from` datetime NOT NULL,
  `reserved_to` datetime NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD PRIMARY KEY (`plan_id`);

--
-- Indexes for table `diet_plan_meals`
--
ALTER TABLE `diet_plan_meals`
  ADD PRIMARY KEY (`meal_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_reservations`
--
ALTER TABLE `user_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `diet_plans`
--
ALTER TABLE `diet_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `diet_plan_meals`
--
ALTER TABLE `diet_plan_meals`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_reservations`
--
ALTER TABLE `user_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `diet_plan_meals`
--
ALTER TABLE `diet_plan_meals`
  ADD CONSTRAINT `diet_plan_meals_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `diet_plans` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `inventory` (`id`);

--
-- Constraints for table `user_reservations`
--
ALTER TABLE `user_reservations`
  ADD CONSTRAINT `user_reservations_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `inventory` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
