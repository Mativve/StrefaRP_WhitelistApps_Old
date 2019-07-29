-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2019 at 01:46 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apps`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE `apps` (
  `id` int(11) NOT NULL,
  `birthday` longtext,
  `forumProfile` longtext,
  `steamId` longtext,
  `stream` longtext,
  `rules` longtext,
  `rp` longtext,
  `pastRpCharacter` longtext,
  `currentRpCharacter` longtext NOT NULL,
  `shop` longtext,
  `car` longtext,
  `medo` longtext,
  `tweet` longtext,
  `revenge` longtext,
  `bw` longtext,
  `metagaming` longtext,
  `powergaming` longtext,
  `forget` longtext,
  `crash` longtext,
  `discord` longtext,
  `discordId` longtext,
  `email` longtext,
  `createDate` datetime(3) DEFAULT NULL,
  `deny` longtext,
  `denyDate` longtext,
  `msgSend` longtext,
  `isAccepted` longtext,
  `addedToWhitelist` longtext,
  `isWatched` int(11) NOT NULL,
  `watchExpire` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user` varchar(150) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `discord_id` bigint(32) NOT NULL,
  `reason` varchar(320) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `reason_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `app_discord` varchar(320) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `time_executed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apps`
--
ALTER TABLE `apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
