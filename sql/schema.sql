-- Database creation schema for EduShare
CREATE DATABASE IF NOT EXISTS `ressources_pedagogiques` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ressources_pedagogiques`;

-- Table for users (if not already existing)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL, -- 'enseignant', 'etudiant'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for resources conforming to the requested schema
CREATE TABLE IF NOT EXISTS `ressources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `type` ENUM('PDF', 'vidéo', 'audio', 'lien') NOT NULL,
  `URL_fichier` VARCHAR(255) NOT NULL,
  `version` INT DEFAULT 1,
  `id_matiere` VARCHAR(100) NOT NULL,
  `id_niveau` VARCHAR(100) NOT NULL,
  `id_enseignant` INT NOT NULL,
  `visibilite` ENUM('public', 'inscrit', 'privatif') DEFAULT 'public',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_enseignant`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Migration script (run this if you already have the database and want to migrate your table structure)
-- WARNING: Ensure you have backed up your database before running schema updates.
--
-- ALTER TABLE `ressources` 
--   CHANGE COLUMN `fichier` `URL_fichier` VARCHAR(255) NOT NULL,
--   CHANGE COLUMN `matiere` `id_matiere` VARCHAR(100) NOT NULL,
--   CHANGE COLUMN `niveau` `id_niveau` VARCHAR(100) NOT NULL,
--   CHANGE COLUMN `user_id` `id_enseignant` INT NOT NULL,
--   ADD COLUMN `version` INT DEFAULT 1 AFTER `URL_fichier`,
--   ADD COLUMN `visibilite` ENUM('public', 'inscrit', 'privatif') DEFAULT 'public' AFTER `id_enseignant`,
--   MODIFY COLUMN `type` ENUM('PDF', 'vidéo', 'audio', 'lien') NOT NULL;
