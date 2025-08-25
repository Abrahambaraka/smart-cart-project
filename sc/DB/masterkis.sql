-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 30 juin 2025 à 20:15
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `masterkis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `masterkis`;

-- --------------------------------------------------------
-- Table des Écoles
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ecoles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_ecole` varchar(255) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_ecole` (`nom_ecole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table des Utilisateurs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `matricule` varchar(255) NOT NULL,
  `type` enum('superadmin','school','student') NOT NULL,
  `ecole_id` int(10) UNSIGNED DEFAULT NULL,
  `classe_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `ecole_id` (`ecole_id`),
  KEY `classe_id` (`classe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Donnée initiale pour le Super Administrateur
--
INSERT IGNORE INTO `users` (`id`, `nom`, `prenom`, `email`, `telephone`, `matricule`, `type`, `ecole_id`, `classe_id`) VALUES
(1, 'Super', 'Admin', 'superadmin@smartcongo.com', NULL, 'superadmin2025', 'superadmin', NULL, NULL);

-- --------------------------------------------------------
-- Table des Classes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `classes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_classe` varchar(100) NOT NULL,
  `ecole_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ecole_id` (`ecole_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Contraintes pour les tables
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL;

ALTER TABLE `classes`
  ADD CONSTRAINT `fk_classe_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;