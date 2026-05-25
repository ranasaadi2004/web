
CREATE DATABASE IF NOT EXISTS `ressources_pedagogiques` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ressources_pedagogiques`;


CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

