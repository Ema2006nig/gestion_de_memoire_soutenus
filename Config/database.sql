-- ============================================================
-- BASE DE DONNÉES — Mémoires Soutenus FINAL
-- UATM GASA Formation
-- ============================================================

CREATE DATABASE IF NOT EXISTS memoires_soutenus
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE memoires_soutenus;

CREATE TABLE utilisateur (
    idUtilisateur VARCHAR(50) PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    role ENUM('ETUDIANT','PROFESSEUR','DE','BIBLIOTHECAIRE') NOT NULL DEFAULT 'ETUDIANT',
    dateEnregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE etudiant (
    idUtilisateur VARCHAR(50) PRIMARY KEY,
    dateNaissance DATE,
    filiere VARCHAR(100),
    option_etu VARCHAR(100),
    niveau ENUM('L1','L2','L3','M1','M2'),
    peutUploader TINYINT(1) DEFAULT 0,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

CREATE TABLE professeur (
    idUtilisateur VARCHAR(50) PRIMARY KEY,
    specialite VARCHAR(100),
    grade VARCHAR(100),
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

CREATE TABLE de (
    idUtilisateur VARCHAR(50) PRIMARY KEY,
    bureau VARCHAR(100),
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

CREATE TABLE bibliothecaire (
    idUtilisateur VARCHAR(50) PRIMARY KEY,
    bureau VARCHAR(100),
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

-- Délégation globale : le DE peut déléguer toute la publication au bibliothécaire
CREATE TABLE delegation_globale (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idDE VARCHAR(50) NOT NULL,
    idBibliothecaire VARCHAR(50) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idDE) REFERENCES utilisateur(idUtilisateur),
    FOREIGN KEY (idBibliothecaire) REFERENCES utilisateur(idUtilisateur)
);

CREATE TABLE compte (
    idCompte VARCHAR(20) PRIMARY KEY,
    idUtilisateur VARCHAR(50) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    motDePasse VARCHAR(255) NOT NULL,
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

CREATE TABLE memoire (
    idMemoire VARCHAR(20) PRIMARY KEY,
    idCompte VARCHAR(20) NOT NULL,
    idProf VARCHAR(50) NOT NULL,
    theme VARCHAR(255) NOT NULL,
    anneeAcademique VARCHAR(9) NOT NULL,
    centre VARCHAR(100) NOT NULL,
    filiere VARCHAR(100),
    option_mem VARCHAR(100),
    type ENUM('ANCIEN','NOUVEAU') NOT NULL DEFAULT 'NOUVEAU',
    statut ENUM('EN_ATTENTE','EN_ATTENTE_DIRECTION','VALIDE','REJETE') NOT NULL DEFAULT 'EN_ATTENTE',
    dateValidation DATE,
    commentaireJury TEXT,
    visible TINYINT(1) DEFAULT 1,
    nbLikes INT DEFAULT 0,
    fichierPdf VARCHAR(255),
    dateUpload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idCompte) REFERENCES compte(idCompte) ON DELETE CASCADE,
    FOREIGN KEY (idProf) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

-- Co-auteurs d'un mémoire
CREATE TABLE co_auteur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idMemoire VARCHAR(20) NOT NULL,
    idCompte VARCHAR(20) NOT NULL,
    statut ENUM('EN_ATTENTE','ACCEPTE','REFUSE') NOT NULL DEFAULT 'EN_ATTENTE',
    dateInvitation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_coauteur (idMemoire, idCompte),
    FOREIGN KEY (idMemoire) REFERENCES memoire(idMemoire) ON DELETE CASCADE,
    FOREIGN KEY (idCompte) REFERENCES compte(idCompte) ON DELETE CASCADE
);

CREATE TABLE commentaire (
    idCommentaire VARCHAR(20) PRIMARY KEY,
    idCompte VARCHAR(20) NOT NULL,
    idMemoire VARCHAR(20) NOT NULL,
    contenu TEXT NOT NULL,
    note FLOAT DEFAULT 0,
    dateCommentaire TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idCompte) REFERENCES compte(idCompte) ON DELETE CASCADE,
    FOREIGN KEY (idMemoire) REFERENCES memoire(idMemoire) ON DELETE CASCADE
);

CREATE TABLE likes (
    idLike VARCHAR(20) PRIMARY KEY,
    idCompte VARCHAR(20) NOT NULL,
    idMemoire VARCHAR(20) NOT NULL,
    dateLike TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (idCompte, idMemoire),
    FOREIGN KEY (idCompte) REFERENCES compte(idCompte) ON DELETE CASCADE,
    FOREIGN KEY (idMemoire) REFERENCES memoire(idMemoire) ON DELETE CASCADE
);

CREATE TABLE notification (
    idNotif VARCHAR(20) PRIMARY KEY,
    idCompte VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    typeNotif VARCHAR(50) NOT NULL,
    dateNotif TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    idMemoire VARCHAR(20),
    FOREIGN KEY (idCompte) REFERENCES compte(idCompte) ON DELETE CASCADE,
    FOREIGN KEY (idMemoire) REFERENCES memoire(idMemoire)
);

CREATE TABLE IF NOT EXISTS password_reset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idCompte VARCHAR(20) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    dateCreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dateExpiration DATETIME NOT NULL,
    utilise BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (idCompte) REFERENCES compte(idCompte) ON DELETE CASCADE
);

-- ============================================================
-- DONNÉES DE TEST  |  Mot de passe universel : "password"
-- ============================================================

INSERT INTO utilisateur VALUES
('DE001','Moukouyou','Jean-Pierre','de@uatm.ga','DE',NOW()),
('BIB001','Ndong','Claire','biblio@uatm.ga','BIBLIOTHECAIRE',NOW()),
-- Professeurs GE
('PROF001','Martin','Paul','paul.martin@uatm.ga','PROFESSEUR',NOW()),
('PROF002','Diallo','Aminata','aminata.diallo@uatm.ga','PROFESSEUR',NOW()),
('PROF003','Nkoghe','Sabine','sabine.nkoghe@uatm.ga','PROFESSEUR',NOW()),
('PROF004','Mba','Ruth','ruth.mba@uatm.ga','PROFESSEUR',NOW()),
('PROF005','Obiang','Henri','henri.obiang@uatm.ga','PROFESSEUR',NOW()),
-- Professeurs Gestion
('PROF006','Tchibozo','Marcel','marcel.tchibozo@uatm.ga','PROFESSEUR',NOW()),
('PROF007','Moussavou','Anne','anne.moussavou@uatm.ga','PROFESSEUR',NOW()),
('PROF008','Ondo','Stéphane','stephane.ondo@uatm.ga','PROFESSEUR',NOW()),
-- Professeurs Droit
('PROF009','Mouity','Christelle','christelle.mouity@uatm.ga','PROFESSEUR',NOW()),
('PROF010','Kombila','Eric','eric.kombila@uatm.ga','PROFESSEUR',NOW()),
-- Professeurs Economie
('PROF011','Nguema','François','francois.nguema@uatm.ga','PROFESSEUR',NOW()),
('PROF012','Bivigou','Nadège','nadege.bivigou@uatm.ga','PROFESSEUR',NOW()),
-- Etudiants diplômés GE
('2021GE0001','Dupont','Jean','jean.dupont@etud.uatm.ga','ETUDIANT',NOW()),
('2021GE0002','Koné','Marie','marie.kone@etud.uatm.ga','ETUDIANT',NOW()),
('2021GE0003','Nze','Pierre','pierre.nze@etud.uatm.ga','ETUDIANT',NOW()),
('2021GE0004','Obame','Lucie','lucie.obame@etud.uatm.ga','ETUDIANT',NOW()),
('2021GE0005','Bongo','Patrick','patrick.bongo@etud.uatm.ga','ETUDIANT',NOW()),
-- Etudiants diplômés Gestion
('2021GS0001','Mboumba','Franck','franck.mboumba@etud.uatm.ga','ETUDIANT',NOW()),
('2021GS0002','Nkoghe','Josiane','josiane.nkoghe@etud.uatm.ga','ETUDIANT',NOW()),
('2021GS0003','Kombila','Rodrigue','rodrigue.kombila@etud.uatm.ga','ETUDIANT',NOW()),
-- Etudiants diplômés Droit
('2021DR0001','Leyama','Bertrand','bertrand.leyama@etud.uatm.ga','ETUDIANT',NOW()),
('2021DR0002','Mouele','Sarah','sarah.mouele@etud.uatm.ga','ETUDIANT',NOW()),
-- Etudiants consultants GE
('2023GE0001','Assoumou','Grace','grace.assoumou@etud.uatm.ga','ETUDIANT',NOW()),
('2023GE0002','Mve','Joel','joel.mve@etud.uatm.ga','ETUDIANT',NOW()),
('2023GE0003','Mintsa','Laure','laure.mintsa@etud.uatm.ga','ETUDIANT',NOW()),
-- Etudiants consultants Gestion
('2023GS0001','Meye','Aurelie','aurelie.meye@etud.uatm.ga','ETUDIANT',NOW()),
('2023GS0002','Nguema','Boris','boris.nguema@etud.uatm.ga','ETUDIANT',NOW()),
-- Etudiants consultants Droit
('2023DR0001','Magnagna','Felix','felix.magnagna@etud.uatm.ga','ETUDIANT',NOW()),
('2023DR0002','Kombila','Gisèle','gisele.kombila@etud.uatm.ga','ETUDIANT',NOW());

INSERT INTO de VALUES ('DE001','Bureau 12');
INSERT INTO bibliothecaire VALUES ('BIB001','Bibliothèque centrale');

INSERT INTO professeur VALUES
('PROF001','Génie Logiciel','Docteur'),
('PROF002','Réseaux','Maître de Conférences'),
('PROF003','Bases de données','Maître-Assistant'),
('PROF004','Intelligence Artificielle','Docteur'),
('PROF005','Systèmes embarqués','Docteur'),
('PROF006','Finance','Professeur Titulaire'),
('PROF007','Comptabilité','Professeur Titulaire'),
('PROF008','Marketing','Maître de Conférences'),
('PROF009','Droit des affaires','Docteur'),
('PROF010','Droit civil','Maître de Conférences'),
('PROF011','Econométrie','Maître-Assistant'),
('PROF012','Macroéconomie','Docteur');

INSERT INTO etudiant VALUES
-- Diplômés GE (peutUploader=1)
('2021GE0001','2000-05-15','Génie Électrique','Système informatique et logiciel','M2',1),
('2021GE0002','2001-03-22','Génie Électrique','Système industriel','M2',1),
('2021GE0003','1999-11-30','Génie Électrique','Système informatique et logiciel','M2',1),
('2021GE0004','2000-07-18','Génie Électrique','Système industriel','M2',1),
('2021GE0005','2000-12-03','Génie Électrique','Agronomie','M2',1),
-- Diplômés Gestion
('2021GS0001','2002-02-11','Sciences de Gestion','Comptabilité','L3',1),
('2021GS0002','2002-05-30','Sciences de Gestion','Finance','L3',1),
('2021GS0003','2001-11-17','Sciences de Gestion','Marketing','L3',1),
-- Diplômés Droit
('2021DR0001','2000-10-08','Droit','Droit des affaires','M2',1),
('2021DR0002','2001-04-15','Droit','Droit civil','M2',1),
-- Consultants GE
('2023GE0001','2002-09-14','Génie Électrique','Système informatique et logiciel','M1',0),
('2023GE0002','2003-01-07','Génie Électrique','Système industriel','M1',0),
('2023GE0003','2002-06-23','Génie Électrique','Agronomie','L3',0),
-- Consultants Gestion
('2023GS0001','2002-05-06','Sciences de Gestion','Comptabilité','M1',0),
('2023GS0002','2003-09-30','Sciences de Gestion','Finance','M1',0),
-- Consultants Droit
('2023DR0001','2002-06-04','Droit','Droit civil','L3',0),
('2023DR0002','2003-01-19','Droit','Droit des affaires','L3',0);

INSERT INTO compte VALUES
('CPT_DE001','DE001','admin_de','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_BIB001','BIB001','bibliothecaire','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_P001','PROF001','prof.martin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_P002','PROF002','prof.diallo','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_P006','PROF006','prof.tchibozo','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_E001','2021GE0001','jean.dupont','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_E002','2021GE0002','marie.kone','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_E003','2021GE0003','pierre.nze','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_E006','2021GS0001','franck.mboumba','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_E009','2021DR0001','bertrand.leyama','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_C001','2023GE0001','grace.assoumou','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_C004','2023GS0001','aurelie.meye','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
('CPT_C006','2023DR0001','felix.magnagna','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW());
