CREATE DATABASE gestion_memoires;
USE gestion_memoires;

-- =====================================
-- TABLE DES UTILISATEURS
-- =====================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(50) UNIQUE NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    role ENUM(
        'etudiant',
        'professeur',
        'commentateur',
        'direction',
        'admin'
    ) NOT NULL,

    telephone VARCHAR(20),
    photo VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================
-- TABLE DES MEMOIRES
-- =====================================

CREATE TABLE memoires (
    id INT AUTO_INCREMENT PRIMARY KEY,

    titre VARCHAR(255) NOT NULL,

    resume TEXT,

    mot_cle VARCHAR(255),

    fichier_pdf VARCHAR(255) NOT NULL,

    statut ENUM(
        'soumis',
        'en_evaluation',
        'corrige',
        'valide_professeur',
        'valide_direction',
        'archive',
        'refuse'
    ) DEFAULT 'soumis',

    etudiant_id INT NOT NULL,

    date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (etudiant_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================
-- ENCADREMENT
-- =====================================

CREATE TABLE encadrements (
    id INT AUTO_INCREMENT PRIMARY KEY,

    memoire_id INT NOT NULL,
    professeur_id INT NOT NULL,

    date_affectation DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (memoire_id)
        REFERENCES memoires(id)
        ON DELETE CASCADE,

    FOREIGN KEY (professeur_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================
-- COMMENTAIRES
-- =====================================

CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,

    memoire_id INT NOT NULL,

    auteur_id INT NOT NULL,

    contenu TEXT NOT NULL,

    type_commentaire ENUM(
        'professeur',
        'commentateur'
    ) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (memoire_id)
        REFERENCES memoires(id)
        ON DELETE CASCADE,

    FOREIGN KEY (auteur_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================
-- ANNOTATIONS
-- =====================================

CREATE TABLE annotations (
    id INT AUTO_INCREMENT PRIMARY KEY,

    memoire_id INT NOT NULL,

    commentateur_id INT NOT NULL,

    page_pdf INT,

    annotation TEXT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (memoire_id)
        REFERENCES memoires(id)
        ON DELETE CASCADE,

    FOREIGN KEY (commentateur_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================
-- VALIDATIONS
-- =====================================

CREATE TABLE validations (
    id INT AUTO_INCREMENT PRIMARY KEY,

    memoire_id INT NOT NULL,

    validateur_id INT NOT NULL,

    role_validateur ENUM(
        'professeur',
        'direction'
    ) NOT NULL,

    decision ENUM(
        'valide',
        'refuse'
    ) NOT NULL,

    remarque TEXT,

    date_validation DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (memoire_id)
        REFERENCES memoires(id)
        ON DELETE CASCADE,

    FOREIGN KEY (validateur_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================
-- NOTIFICATIONS
-- =====================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    titre VARCHAR(255),

    message TEXT NOT NULL,

    lu BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================
-- ARCHIVES
-- =====================================

CREATE TABLE archives (
    id INT AUTO_INCREMENT PRIMARY KEY,

    memoire_id INT NOT NULL UNIQUE,

    date_archive DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (memoire_id)
        REFERENCES memoires(id)
        ON DELETE CASCADE
);

-- =====================================
-- PERMISSIONS
-- =====================================

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nom_permission VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,

    role ENUM(
        'etudiant',
        'professeur',
        'commentateur',
        'direction',
        'admin'
    ) NOT NULL,

    permission_id INT NOT NULL,

    FOREIGN KEY (permission_id)
        REFERENCES permissions(id)
        ON DELETE CASCADE
);