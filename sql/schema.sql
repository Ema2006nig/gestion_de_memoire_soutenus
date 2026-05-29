-- Base de donnees : gestion_memoires
CREATE DATABASE IF NOT EXISTS gestion_memoires CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_memoires;

CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('etudiant','professeur','direction','commentateur','service_technique') NOT NULL,
  matricule VARCHAR(50) NULL,
  grade VARCHAR(50) NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE memoires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  etudiant_id INT NOT NULL,
  professeur_id INT NULL,
  titre VARCHAR(255) NOT NULL,
  resume TEXT NULL,
  fichier VARCHAR(255) NOT NULL,
  statut ENUM('soumis','valide_prof','refuse_prof','valide_direction','en_ligne') NOT NULL DEFAULT 'soumis',
  date_soumission DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (professeur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE commentaires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memoire_id INT NOT NULL,
  auteur_id INT NOT NULL,
  contenu TEXT NOT NULL,
  cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE CASCADE,
  FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  lu TINYINT(1) NOT NULL DEFAULT 0,
  cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comptes de demonstration (mot de passe : Passw0rd!)
INSERT INTO utilisateurs (nom,email,mot_de_passe,role,matricule,grade) VALUES
('Admin Technique','admin@univ.local','$2y$10$e0NRiQwUu0H3l9k9k9k9kuQ8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q','service_technique',NULL,NULL),
('Direction Etudes','direction@univ.local','$2y$10$e0NRiQwUu0H3l9k9k9k9kuQ8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q','direction',NULL,NULL),
('Prof Dupont','prof@univ.local','$2y$10$e0NRiQwUu0H3l9k9k9k9kuQ8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q','professeur',NULL,'Maitre de conferences'),
('Etudiant Test','etudiant@univ.local','$2y$10$e0NRiQwUu0H3l9k9k9k9kuQ8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q','etudiant','MAT2025',NULL),
('Commentateur','commentateur@univ.local','$2y$10$e0NRiQwUu0H3l9k9k9k9kuQ8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q8Q','commentateur',NULL,NULL);
