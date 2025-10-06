-- phpMyAdmin SQL Dump
-- version 4.9.6
-- https://www.phpmyadmin.net/
--
-- Host: 7k1pbc.myd.infomaniak.com
-- Erstellungszeit: 16. Sep 2025 um 11:29
-- Server-Version: 10.6.19-MariaDB-deb11-log
-- PHP-Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `7k1pbc_im3`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `regtime` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;

--
-- Daten für Tabelle `User`
--

INSERT INTO `User` (`id`, `firstname`, `lastname`, `email`, `regtime`) VALUES
(1, 'Aline', 'Weisser', 'aline@fhgr.ch', '2024-02-10 09:44:57'),
(2, 'Urs', 'Thöny', 'urs@fhgr.ch', '2024-03-10 16:17:03'),
(3, 'Wolfgang', 'Bock', 'wolfgang.bock@fhgr.ch', '2024-09-16 07:07:45'),
(4, 'John', 'Doe', 'jd@gmail.com', '2024-09-16 13:25:41'),
(5, 'Thomas', 'Blaustein', 'thomas@blaustein.ch', '2025-09-14 16:52:24');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `weather_data`
--

CREATE TABLE `weather_data` (
  `id` int(11) UNSIGNED NOT NULL,
  `location` varchar(64) NOT NULL,
  `temperature_celsius` float NOT NULL,
  `rain` int(5) NOT NULL,
  `showers` int(5) NOT NULL,
  `snowfall` int(5) NOT NULL,
  `cloud_cover` int(5) NOT NULL,
  `weather_condition` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `weather_data`
--

INSERT INTO `weather_data` (`id`, `location`, `temperature_celsius`, `rain`, `showers`, `snowfall`, `cloud_cover`, `weather_condition`, `created_at`) VALUES
(7, 'Bern', 22, 0, 0, 0, 95, 'bewölkt', '2025-09-15 16:16:17'),
(8, 'Chur', 21.78, 0, 0, 0, 55, 'teilweise bewölkt', '2025-09-15 16:16:17'),
(9, 'Zürich', 23.17, 0, 0, 0, 100, 'bewölkt', '2025-09-15 16:16:17'),
(10, 'Bern', 21.83, 0, 0, 0, 100, 'regnerisch', '2025-09-15 17:00:01'),
(11, 'Chur', 20.89, 0, 0, 0, 57, 'teilweise bewölkt', '2025-09-15 17:00:01'),
(12, 'Zürich', 23.78, 0, 0, 0, 99, 'bewölkt', '2025-09-15 17:00:01'),
(13, 'Bern', 19.06, 0, 0, 0, 72, 'bewölkt', '2025-09-15 18:00:07'),
(14, 'Chur', 18.67, 0, 0, 0, 36, 'teilweise bewölkt', '2025-09-15 18:00:07'),
(15, 'Zürich', 19.78, 0, 0, 0, 98, 'bewölkt', '2025-09-15 18:00:07'),
(16, 'Bern', 18.06, 0, 0, 0, 84, 'bewölkt', '2025-09-15 19:00:13'),
(17, 'Chur', 17, 0, 0, 0, 63, 'teilweise bewölkt', '2025-09-15 19:00:13'),
(18, 'Zürich', 18.17, 0, 0, 0, 94, 'bewölkt', '2025-09-15 19:00:13'),
(19, 'Bern', 17.44, 0, 0, 0, 92, 'bewölkt', '2025-09-15 20:00:07'),
(20, 'Chur', 17.17, 0, 0, 0, 60, 'teilweise bewölkt', '2025-09-15 20:00:07'),
(21, 'Zürich', 17.56, 0, 0, 0, 80, 'bewölkt', '2025-09-15 20:00:07'),
(22, 'Bern', 17.22, 0, 0, 0, 86, 'bewölkt', '2025-09-15 21:00:10'),
(23, 'Chur', 17.11, 0, 0, 0, 97, 'bewölkt', '2025-09-15 21:00:10'),
(24, 'Zürich', 17.5, 0, 0, 0, 94, 'bewölkt', '2025-09-15 21:00:10'),
(25, 'Bern', 17.28, 0, 0, 0, 100, 'bewölkt', '2025-09-15 22:00:06'),
(26, 'Chur', 16.61, 0, 0, 0, 45, 'teilweise bewölkt', '2025-09-15 22:00:06'),
(27, 'Zürich', 17.5, 0, 0, 0, 81, 'bewölkt', '2025-09-15 22:00:06'),
(28, 'Bern', 16.78, 0, 0, 0, 89, 'bewölkt', '2025-09-15 23:00:20'),
(29, 'Chur', 16.94, 0, 0, 0, 78, 'bewölkt', '2025-09-15 23:00:20'),
(30, 'Zürich', 16.72, 0, 0, 0, 100, 'bewölkt', '2025-09-15 23:00:20'),
(31, 'Bern', 15.72, 0, 0, 0, 93, 'bewölkt', '2025-09-16 00:00:08'),
(32, 'Chur', 16.22, 0, 0, 0, 96, 'bewölkt', '2025-09-16 00:00:08'),
(33, 'Zürich', 16.5, 0, 0, 0, 91, 'bewölkt', '2025-09-16 00:00:08'),
(34, 'Bern', 14.44, 0, 0, 0, 100, 'bewölkt', '2025-09-16 01:00:06'),
(35, 'Chur', 15.44, 1, 0, 0, 100, 'regnerisch', '2025-09-16 01:00:06'),
(36, 'Zürich', 15.72, 0, 0, 0, 84, 'bewölkt', '2025-09-16 01:00:06'),
(37, 'Bern', 14.28, 0, 0, 0, 93, 'bewölkt', '2025-09-16 02:00:16'),
(38, 'Chur', 15.22, 0, 0, 0, 98, 'regnerisch', '2025-09-16 02:00:16'),
(39, 'Zürich', 15.33, 0, 0, 0, 56, 'teilweise bewölkt', '2025-09-16 02:00:16'),
(40, 'Bern', 14.06, 0, 0, 0, 93, 'bewölkt', '2025-09-16 03:00:09'),
(41, 'Chur', 14.44, 0, 0, 0, 89, 'bewölkt', '2025-09-16 03:00:09'),
(42, 'Zürich', 14.78, 0, 0, 0, 78, 'bewölkt', '2025-09-16 03:00:09'),
(43, 'Bern', 13.39, 0, 0, 0, 95, 'bewölkt', '2025-09-16 04:00:06'),
(44, 'Chur', 14, 0, 0, 0, 93, 'bewölkt', '2025-09-16 04:00:06'),
(45, 'Zürich', 14.44, 0, 0, 0, 68, 'teilweise bewölkt', '2025-09-16 04:00:06'),
(46, 'Bern', 13.22, 0, 0, 0, 75, 'bewölkt', '2025-09-16 05:00:22'),
(47, 'Chur', 13.5, 0, 0, 0, 78, 'bewölkt', '2025-09-16 05:00:22'),
(48, 'Zürich', 14.28, 0, 0, 0, 63, 'teilweise bewölkt', '2025-09-16 05:00:22'),
(49, 'Bern', 13.22, 0, 0, 0, 75, 'bewölkt', '2025-09-16 05:04:06'),
(50, 'Chur', 13.5, 0, 0, 0, 78, 'bewölkt', '2025-09-16 05:04:06'),
(51, 'Zürich', 14.28, 0, 0, 0, 63, 'teilweise bewölkt', '2025-09-16 05:04:06'),
(52, 'Bern', 13.17, 0, 0, 0, 87, 'bewölkt', '2025-09-16 06:00:05'),
(53, 'Chur', 13.44, 0, 0, 0, 68, 'teilweise bewölkt', '2025-09-16 06:00:05'),
(54, 'Zürich', 14, 0, 0, 0, 89, 'bewölkt', '2025-09-16 06:00:05'),
(55, 'Bern', 13.44, 0, 0, 0, 71, 'bewölkt', '2025-09-16 07:00:11'),
(56, 'Chur', 14.28, 0, 0, 0, 89, 'bewölkt', '2025-09-16 07:00:11'),
(57, 'Zürich', 14.11, 0, 0, 0, 78, 'bewölkt', '2025-09-16 07:00:11'),
(58, 'Bern', 14.22, 0, 0, 0, 99, 'bewölkt', '2025-09-16 08:00:07'),
(59, 'Chur', 15.39, 0, 0, 0, 100, 'bewölkt', '2025-09-16 08:00:07'),
(60, 'Zürich', 14.94, 0, 0, 0, 98, 'bewölkt', '2025-09-16 08:00:07'),
(61, 'Bern', 14.72, 0, 0, 0, 100, 'bewölkt', '2025-09-16 09:00:21'),
(62, 'Chur', 15.83, 0, 0, 0, 100, 'bewölkt', '2025-09-16 09:00:21'),
(63, 'Zürich', 15.72, 0, 0, 0, 100, 'bewölkt', '2025-09-16 09:00:21');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indizes für die Tabelle `weather_data`
--
ALTER TABLE `weather_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `weather_data`
--
ALTER TABLE `weather_data`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
