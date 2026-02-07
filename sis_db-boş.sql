-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Hazırlanma Vaxtı: 30 Yan, 2026 saat 19:09
-- Server versiyası: 10.4.32-MariaDB
-- PHP Versiyası: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Verilənlər Bazası: `sis_db`
--

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `qiymetlendirmeler`
--

CREATE TABLE `qiymetlendirmeler` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `title` varchar(50) NOT NULL,
  `score` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sxemi çıxarılan cedvel `qiymetlendirmeler`
--

INSERT INTO `qiymetlendirmeler` (`id`, `code`, `title`, `score`) VALUES
(1, 'ela', 'Əla', 3),
(2, 'yaxsi', 'Yaxşı', 2),
(3, 'kafi', 'Kafi', 1);

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `sectors`
--

CREATE TABLE `sectors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','isci') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fullname` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sxemi çıxarılan cedvel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `is_active`, `password`, `role`, `created_at`, `fullname`, `last_login`) VALUES
(1, 'a.hamidova', NULL, 1, '$2y$10$u4QJ4dBd2rShbdzSXpux6uclTnV87j/GHwHpr6L00tVJees7J76t2', 'superadmin', '2026-01-30 18:05:14', 'Aynur Həmidova', NULL);

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `weekly_works`
--

CREATE TABLE `weekly_works` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `worked_days` int(11) DEFAULT 0,
  `veten_muraciyet` int(11) DEFAULT 0,
  `teskilat_muraciyet` int(11) DEFAULT 0,
  `sorqu` int(11) DEFAULT 0,
  `imtina` int(11) DEFAULT 0,
  `arayish` int(11) DEFAULT 0,
  `geri_qaytarilan` int(11) DEFAULT 0,
  `imtina_gulhuseyn` int(11) DEFAULT 0,
  `imtina_aynur` int(11) DEFAULT 0,
  `imtina_adil` int(11) DEFAULT 0,
  `tesekkur` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('is_rejim','mezuniyyet','xestelik','ezamiyyet') DEFAULT 'is_rejim',
  `qiymetlendirme_id` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Cədvəl üçün indekslər `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Cədvəl üçün indekslər `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Cədvəl üçün indekslər `qiymetlendirmeler`
--
ALTER TABLE `qiymetlendirmeler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Cədvəl üçün indekslər `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`id`);

--
-- Cədvəl üçün indekslər `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Cədvəl üçün indekslər `weekly_works`
--
ALTER TABLE `weekly_works`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `fk_qiymetlendirme` (`qiymetlendirme_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- Cədvəl üçün AUTO_INCREMENT `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Cədvəl üçün AUTO_INCREMENT `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Cədvəl üçün AUTO_INCREMENT `qiymetlendirmeler`
--
ALTER TABLE `qiymetlendirmeler`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Cədvəl üçün AUTO_INCREMENT `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Cədvəl üçün AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Cədvəl üçün AUTO_INCREMENT `weekly_works`
--
ALTER TABLE `weekly_works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`),
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`);

--
-- Constraints for table `weekly_works`
--
ALTER TABLE `weekly_works`
  ADD CONSTRAINT `fk_qiymetlendirme` FOREIGN KEY (`qiymetlendirme_id`) REFERENCES `qiymetlendirmeler` (`id`),
  ADD CONSTRAINT `weekly_works_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
