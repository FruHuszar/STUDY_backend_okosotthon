-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 13, 2026 at 02:48 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `otthon_plusz`
--
CREATE DATABASE IF NOT EXISTS `otthon_plusz` DEFAULT CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
USE `otthon_plusz`;

-- --------------------------------------------------------

--
-- Table structure for table `dugalj`
--

DROP TABLE IF EXISTS `dugalj`;
CREATE TABLE IF NOT EXISTS `dugalj` (
  `eszkoz_id` int(11) NOT NULL,
  `aktualis_fogyasztas` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`eszkoz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eszkoz`
--

DROP TABLE IF EXISTS `eszkoz`;
CREATE TABLE IF NOT EXISTS `eszkoz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `megnevezes` varchar(100) NOT NULL,
  `eszkoztipus_id` int(11) NOT NULL,
  `allapot` tinyint(1) DEFAULT NULL,
  `termekszam` varchar(150) DEFAULT NULL,
  `garancia_kezdete` date DEFAULT NULL,
  `garancia_vege` date DEFAULT NULL,
  `helyiseg_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eszkoztipus_id` (`eszkoztipus_id`),
  KEY `helyiseg_id` (`helyiseg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eszkoztipus`
--

DROP TABLE IF EXISTS `eszkoztipus`;
CREATE TABLE IF NOT EXISTS `eszkoztipus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `megnevezes` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `felhasznalo`
--

DROP TABLE IF EXISTS `felhasznalo`;
CREATE TABLE IF NOT EXISTS `felhasznalo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL,
  `jelszo` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `helyiseg`
--

DROP TABLE IF EXISTS `helyiseg`;
CREATE TABLE IF NOT EXISTS `helyiseg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `megnevezes` varchar(100) NOT NULL,
  `terulet` decimal(6,2) DEFAULT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `felhasznalo_id` (`felhasznalo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lampa`
--

DROP TABLE IF EXISTS `lampa`;
CREATE TABLE IF NOT EXISTS `lampa` (
  `eszkoz_id` int(11) NOT NULL,
  `fenyero` int(11) DEFAULT NULL,
  PRIMARY KEY (`eszkoz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meres`
--

DROP TABLE IF EXISTS `meres`;
CREATE TABLE IF NOT EXISTS `meres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eszkoz_id` int(11) NOT NULL,
  `idobelyeg` timestamp NOT NULL DEFAULT current_timestamp(),
  `meres_tipusa` varchar(50) NOT NULL,
  `ertek` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `meres_ibfk_1` (`eszkoz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nap`
--

DROP TABLE IF EXISTS `nap`;
CREATE TABLE IF NOT EXISTS `nap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `termosztat`
--

DROP TABLE IF EXISTS `termosztat`;
CREATE TABLE IF NOT EXISTS `termosztat` (
  `eszkoz_id` int(11) NOT NULL,
  `celhomerseklet` decimal(4,1) DEFAULT NULL,
  `aktualis_homerseklet` decimal(4,1) DEFAULT NULL,
  `uzemmod` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`eszkoz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utemezes`
--

DROP TABLE IF EXISTS `utemezes`;
CREATE TABLE IF NOT EXISTS `utemezes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eszkoz_id` int(11) NOT NULL,
  `letrehozva_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `modositva_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `kezdo_ido` time DEFAULT NULL,
  `zaro_ido` time DEFAULT NULL,
  `cel_allapot` tinyint(1) DEFAULT NULL,
  `cel_ertek` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eszkoz_id` (`eszkoz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utemezes_nap`
--

DROP TABLE IF EXISTS `utemezes_nap`;
CREATE TABLE IF NOT EXISTS `utemezes_nap` (
  `utemezes_id` int(11) NOT NULL,
  `nap_id` int(11) NOT NULL,
  PRIMARY KEY (`utemezes_id`,`nap_id`),
  KEY `nap_id` (`nap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dugalj`
--
ALTER TABLE `dugalj`
  ADD CONSTRAINT `dugalj_ibfk_1` FOREIGN KEY (`eszkoz_id`) REFERENCES `eszkoz` (`id`);

--
-- Constraints for table `eszkoz`
--
ALTER TABLE `eszkoz`
  ADD CONSTRAINT `eszkoz_ibfk_1` FOREIGN KEY (`eszkoztipus_id`) REFERENCES `eszkoztipus` (`id`),
  ADD CONSTRAINT `eszkoz_ibfk_2` FOREIGN KEY (`helyiseg_id`) REFERENCES `helyiseg` (`id`);

--
-- Constraints for table `helyiseg`
--
ALTER TABLE `helyiseg`
  ADD CONSTRAINT `helyiseg_ibfk_1` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalo` (`id`);

--
-- Constraints for table `lampa`
--
ALTER TABLE `lampa`
  ADD CONSTRAINT `lampa_ibfk_1` FOREIGN KEY (`eszkoz_id`) REFERENCES `eszkoz` (`id`);

--
-- Constraints for table `meres`
--
ALTER TABLE `meres`
  ADD CONSTRAINT `meres_ibfk_1` FOREIGN KEY (`eszkoz_id`) REFERENCES `eszkoz` (`id`);

--
-- Constraints for table `termosztat`
--
ALTER TABLE `termosztat`
  ADD CONSTRAINT `termosztat_ibfk_1` FOREIGN KEY (`eszkoz_id`) REFERENCES `eszkoz` (`id`);

--
-- Constraints for table `utemezes`
--
ALTER TABLE `utemezes`
  ADD CONSTRAINT `utemezes_ibfk_1` FOREIGN KEY (`eszkoz_id`) REFERENCES `eszkoz` (`id`);

--
-- Constraints for table `utemezes_nap`
--
ALTER TABLE `utemezes_nap`
  ADD CONSTRAINT `utemezes_nap_ibfk_1` FOREIGN KEY (`utemezes_id`) REFERENCES `utemezes` (`id`),
  ADD CONSTRAINT `utemezes_nap_ibfk_2` FOREIGN KEY (`nap_id`) REFERENCES `nap` (`id`);

  
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
