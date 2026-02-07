-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Hazırlanma Vaxtı: 07 Fev, 2026 saat 19:21
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

--
-- Sxemi çıxarılan cedvel `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `ad`, `soyad`, `sector_id`, `position_id`, `created_at`) VALUES
(1, 2, 'Mikayıl', 'Bayramov', 1, 1, '2026-01-30 18:49:49'),
(2, 3, 'Xanlar', 'Xancanov', 2, 2, '2026-01-30 18:50:39'),
(3, 4, 'Nailə', 'Vəliyeva', 2, 3, '2026-01-30 18:51:34'),
(4, 5, 'Zöhrə', 'Əliyeva', 2, 4, '2026-01-30 18:53:37'),
(5, 6, 'Vəfa', 'Məmmədzadə', 3, 2, '2026-01-30 18:54:09'),
(6, 7, 'Orxan', 'Tapdıqlı', 3, 3, '2026-01-30 18:54:43'),
(7, 8, 'Yeganə', 'Quliyeva', NULL, NULL, '2026-01-30 18:54:58'),
(8, 9, 'Lalə', 'Əlişanova', NULL, NULL, '2026-01-30 18:56:34'),
(9, 10, 'Aysel', 'Yaqubova', NULL, NULL, '2026-01-30 19:05:18'),
(11, 11, 'Cəmilə', 'Ukalayeva', NULL, NULL, '2026-01-30 19:05:52'),
(12, 12, 'Mahir', 'Məmmədov', NULL, NULL, '2026-01-30 19:06:33'),
(13, 13, 'Fidan', 'Hacıyeva', NULL, NULL, '2026-01-30 19:06:52'),
(14, 14, 'Türkan', 'Xəlilova', NULL, NULL, '2026-01-30 19:09:25'),
(15, 15, 'Kafiyə', 'Abdinova', NULL, NULL, '2026-01-30 19:10:24'),
(16, 16, 'Murad', 'Babayev', NULL, NULL, '2026-01-30 19:10:42'),
(17, 17, 'Rəşad', 'Səmədov', NULL, NULL, '2026-01-30 19:11:38');

-- --------------------------------------------------------

--
-- Cədvəl üçün cədvəl strukturu `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sxemi çıxarılan cedvel `positions`
--

INSERT INTO `positions` (`id`, `name`, `created_at`) VALUES
(1, 'Sektor müdiri', '2026-01-30 18:48:01'),
(2, 'Baş məsləhətçi', '2026-01-30 18:48:23'),
(3, 'Böyük məsləhətçi', '2026-01-30 18:48:26'),
(4, 'Aparıcı məsləhətçi', '2026-01-30 18:48:44');

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

--
-- Sxemi çıxarılan cedvel `sectors`
--

INSERT INTO `sectors` (`id`, `name`, `created_at`) VALUES
(1, 'Mülki və siyasi hüquqların müdafiəsi sektoru', '2026-01-30 18:46:24'),
(2, 'İqtisadi, sosial və mədəni hüquqların müdafiəsi sektoru', '2026-01-30 18:46:52'),
(3, 'Şəhid ailələrinin, müharibə iştirakçılarının və miqrantların hüquqlarının müdafiəsi sektoru', '2026-01-30 18:47:05');

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
(1, 'a.hamidova', NULL, 1, '$2y$10$u4QJ4dBd2rShbdzSXpux6uclTnV87j/GHwHpr6L00tVJees7J76t2', 'superadmin', '2026-01-30 18:05:14', 'Aynur Həmidova', '2026-02-07 13:14:58'),
(2, 'm.bayramov', NULL, 1, '$2y$10$4Ma/f9XJSpunEz8XyJ7dzugF5tMmIxch.Hn/sV2YofX9.kZpYp4LC', 'isci', '2026-01-30 18:24:14', 'Mikayıl Bayramov', '2026-02-02 10:15:58'),
(3, 'x.xancanov', NULL, 1, '$2y$10$fo6sS06pc1SCBi3y4qdY4uEQizaEGa1hhI8IQOiBpmKPKPcRxUAkW', 'isci', '2026-01-30 18:30:07', 'Xanlar Xancanov', NULL),
(4, 'n.valiyeva', NULL, 1, '$2y$10$pSN5D766dVRneddq0FFfkuoKXWFomO0yVf.LLoctk9MBRP5kWD.My', 'isci', '2026-01-30 18:30:43', 'Nailə Vəliyeva', NULL),
(5, 'z.aliyeva', NULL, 1, '$2y$10$c0UWNiEd0vnmr.mcfoAIiOWla7EHZBoeyFL.yGIW673XyzmvLeu.i', 'isci', '2026-01-30 18:33:24', 'Zöhrə Əliyeva', NULL),
(6, 'v.mammadzada', NULL, 1, '$2y$10$N7JZWDBZ.iFIsHHk2tGqh.bc8dPi7DmB6OcvM2vUJ7flT4IF1kq.q', 'isci', '2026-01-30 18:33:54', 'Vəfa Məmmədzadə', '2026-02-02 10:06:16'),
(7, 'o.tapdıqlı', NULL, 1, '$2y$10$L/AS2woTMKFDApmf9nxNVejKDaeP3U5gQckx/KQXrZMdcBqKZukia', 'isci', '2026-01-30 18:34:27', 'Orxan Tapdıqlı', '2026-02-02 10:04:21'),
(8, 'y.quliyeva', NULL, 1, '$2y$10$.PHaGb0Ehb20IN1o9ZARwebrjy89h8qZDnQZ2qUiZ8qUlEtzW4PES', 'isci', '2026-01-30 18:35:01', 'Yeganə Quliyeva', '2026-02-03 11:58:10'),
(9, 'l.alishanova', NULL, 1, '$2y$10$9k5wG.7hNzRDuv3FaXyvdumu7cTFQKcQtBurW/3lykeVjckNcyDuy', 'isci', '2026-01-30 18:38:32', 'Lalə Əlişanova', NULL),
(10, 'a.yaqubova', NULL, 1, '$2y$10$ozevIoC3g/zQpbth5S1Yo.V9cVdm9Ma.MFuU/Cy59D7Gjy8h0ygfy', 'isci', '2026-01-30 18:39:05', 'Aysel Yaqubova', NULL),
(11, 'c.ukalayeva', NULL, 1, '$2y$10$yrBAYObHURcX6EvARIGBq.MrUc2lcvXsqGIQrbSM/ob5sHjT7X1gq', 'isci', '2026-01-30 18:39:44', 'Cəmilə Ukalayeva', NULL),
(12, 'm.mammadov', NULL, 1, '$2y$10$zkZB2ETp2h5ks1xgMTMPRuhtbyrP09dMjFaZfj8SRom1l355EOcwq', 'isci', '2026-01-30 18:41:01', 'Mahir Məmmədov', NULL),
(13, 'f.haciyeva', NULL, 1, '$2y$10$kl9pDmPNlhmG4hko0Df/5O69bTHA/DYTGenSyxWM8V.KPzZCM.FYW', 'isci', '2026-01-30 18:41:30', 'Fidan Hacıyeva', '2026-02-06 17:31:16'),
(14, 't.xalilova', NULL, 1, '$2y$10$E.TmyygoOXUkPmyT0Xz76.fXDLXuoh4KQJuZzlnRH.RllZbMfEEl6', 'isci', '2026-01-30 18:41:57', 'Türkan Xəlilova', '2026-02-06 17:51:21'),
(15, 'k.abdinova', NULL, 1, '$2y$10$RVhAzFqq4EVjDtQW4ExtJOUUVWQwrf3ltLMfFqJfa0Pkp0fBXDUHW', 'isci', '2026-01-30 18:42:25', 'Kafiyə Abdinova', '2026-02-06 17:24:07'),
(16, 'm.babayev', NULL, 1, '$2y$10$WeJJz/Hig9xUIvqOa/W9lulUY/7FB1GXtx/KAgjGoFqmGfodLgKJy', 'isci', '2026-01-30 18:42:49', 'Murad Babayev', '2026-02-06 17:31:55'),
(17, 'r.samadov', NULL, 1, '$2y$10$i7V.xSozZ6a/iIJsBqJA2OVRyZHUaPvZSY2P64Y498o96k0Le8T/y', 'isci', '2026-01-30 18:43:16', 'Rəşad Səmədov', NULL),
(18, 'sabina.aliyeva', NULL, 1, '$2y$10$cfVgBexPT88YwgIG/EgPYOWFH1P314jf7nozTRouq3pFTl5oatJb.', 'superadmin', '2026-01-30 18:44:03', 'Səbinə Əliyeva', NULL);

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
-- Sxemi çıxarılan cedvel `weekly_works`
--

INSERT INTO `weekly_works` (`id`, `employee_id`, `start_date`, `end_date`, `worked_days`, `veten_muraciyet`, `teskilat_muraciyet`, `sorqu`, `imtina`, `arayish`, `geri_qaytarilan`, `imtina_gulhuseyn`, `imtina_aynur`, `imtina_adil`, `tesekkur`, `created_at`, `updated_at`, `status`, `qiymetlendirme_id`) VALUES
(1, 13, '2026-02-02', '2026-02-06', 5, 36, 2, 18, 5, 8, 3, 0, 0, 0, 0, '2026-02-06 13:02:23', '2026-02-06 13:02:23', 'is_rejim', NULL),
(2, 15, '2026-02-02', '2026-02-06', 5, 30, 6, 11, 5, 7, 1, 0, 1, 1, 0, '2026-02-06 13:28:39', '2026-02-06 13:28:39', 'is_rejim', NULL),
(3, 16, '2026-02-02', '2026-02-06', 5, 4, 2, 1, 0, 1, 0, 0, 1, 0, 0, '2026-02-06 13:46:59', '2026-02-06 13:46:59', 'is_rejim', NULL),
(4, 14, '2026-02-02', '2026-02-06', 5, 29, 0, 14, 3, 10, 4, 0, 0, 0, 0, '2026-02-06 14:05:37', '2026-02-06 14:05:37', 'is_rejim', NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Cədvəl üçün AUTO_INCREMENT `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Cədvəl üçün AUTO_INCREMENT `qiymetlendirmeler`
--
ALTER TABLE `qiymetlendirmeler`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Cədvəl üçün AUTO_INCREMENT `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Cədvəl üçün AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Cədvəl üçün AUTO_INCREMENT `weekly_works`
--
ALTER TABLE `weekly_works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
