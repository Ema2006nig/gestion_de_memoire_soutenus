<?php

require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/../Repository/DERepository.php';
require_once __DIR__ . '/../Repository/EtudiantRepository.php';
require_once __DIR__ . '/../Repository/ProfesseurRepository.php';
require_once __DIR__ . '/../Repository/CompteRepository.php';
require_once __DIR__ . '/../Repository/MemoireRepository.php';

class DE extends Utilisateur {

    private string $bureau;

    public function __construct(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $bureau
    ) {
        parent::__construct($idUtilisateur, $nom, $prenom, $email);
        $this->bureau = $bureau;
    }

    public function getBureau(): string              { return $this->bureau; }
    public function setBureau(string $b): void       { $this->bureau = $b; }

    // ── Méthodes métier (sans SQL) ──

    // Enregistrer un étudiant dans la liste
    public function enregistrerEtudiant(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $dateNaissance,
        string $filiere,
        string $option_etu,
        string $niveau
    ): bool {
        $data = [
            'idUtilisateur' => $idUtilisateur,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'dateNaissance' => $dateNaissance,
            'filiere' => $filiere,
            'option_etu' => $option_etu,
            'niveau' => $niveau,
            'peutUploader' => 0
        ];
        try {
            EtudiantRepository::create($data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Modifier un étudiant
    public function modifierEtudiant(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $dateNaissance,
        string $filiere,
        string $option_etu,
        string $niveau
    ): bool {
        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'dateNaissance' => $dateNaissance,
            'filiere' => $filiere,
            'option_etu' => $option_etu,
            'niveau' => $niveau
        ];
        return UtilisateurRepository::update($idUtilisateur, $data);
    }

    // Supprimer un étudiant
    public function supprimerEtudiant(string $idUtilisateur): bool {
        return UtilisateurRepository::delete($idUtilisateur);
    }

    // Enregistrer un professeur
    public function enregistrerProf(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $specialite
    ): bool {
        $data = [
            'idUtilisateur' => $idUtilisateur,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'specialite' => $specialite
        ];
        try {
            ProfesseurRepository::create($data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Modifier un professeur
    public function modifierProf(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $specialite
    ): bool {
        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'specialite' => $specialite
        ];
        return ProfesseurRepository::update($idUtilisateur, $data);
    }

    // Supprimer un professeur
    public function supprimerProf(string $idUtilisateur): bool {
        return ProfesseurRepository::delete($idUtilisateur);
    }

    // Créer compte automatiquement pour ancien utilisateur
    public function creerCompteAncien(string $idUtilisateur): bool {
        if (CompteRepository::compteExiste($idUtilisateur)) {
            return true; // Déjà créé
        }
        $idCompte = CompteRepository::create($idUtilisateur, $idUtilisateur, $idUtilisateur);
        return !empty($idCompte);
    }

    // Uploader un ancien mémoire (statut VALIDE directement)
    public function uploaderAncMemoire(
        string $idCompte,
        string $idProf,
        string $theme,
        string $anneeAcademique,
        string $centre,
        string $fichierPdf = ''
    ): bool {
        $idMemoire = MemoireRepository::saveAncien($idCompte, $idProf, $theme, $anneeAcademique, $centre, '', '', $fichierPdf);
        return !empty($idMemoire);
    }

    // Activer l'upload pour un étudiant après soutenance
    public function activerUpload(string $idUtilisateur): bool {
        return EtudiantRepository::activerUpload($idUtilisateur);
    }

    // Désactiver l'upload pour un étudiant
    public function desactiverUpload(string $idUtilisateur): bool {
        return EtudiantRepository::desactiverUpload($idUtilisateur);
    }

    // Lister tous les étudiants
    public function listerEtudiants(): array {
        return EtudiantRepository::listerAvecCompte();
    }

    // Lister tous les profs
    public function listerProfs(): array {
        return ProfesseurRepository::findAll();
    }

    // Importer des étudiants depuis un fichier CSV
    public function importerEtudiantsCSV(string $cheminFichier): array {
        $resultats = ['succes' => 0, 'erreurs' => 0, 'details' => []];

        if (!file_exists($cheminFichier)) {
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Fichier introuvable.";
            return $resultats;
        }

        $fichier = fopen($cheminFichier, 'r');
        if ($fichier === false) {
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Impossible d'ouvrir le fichier.";
            return $resultats;
        }

        // Lire l'en-tête
        $entete = fgetcsv($fichier);
        if ($entete === false) {
            fclose($fichier);
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Fichier vide ou invalide.";
            return $resultats;
        }

        // Vérifier les colonnes requises
        $colonnesRequises = ['idUtilisateur', 'nom', 'prenom', 'email', 'dateNaissance', 'filiere', 'niveau'];
        $colonnesManquantes = array_diff($colonnesRequises, $entete);
        if (!empty($colonnesManquantes)) {
            fclose($fichier);
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Colonnes manquantes : " . implode(', ', $colonnesManquantes);
            return $resultats;
        }

        // Mapper les colonnes
        $index = array_flip($entete);

        $ligne = 1;
        while (($donnees = fgetcsv($fichier)) !== false) {
            $ligne++;

            // Nettoyer les données
            $idUtilisateur = trim($donnees[$index['idUtilisateur']] ?? '');
            $nom = trim($donnees[$index['nom']] ?? '');
            $prenom = trim($donnees[$index['prenom']] ?? '');
            $email = trim($donnees[$index['email']] ?? '');
            $dateNaissance = trim($donnees[$index['dateNaissance']] ?? '');
            $filiere = trim($donnees[$index['filiere']] ?? '');
            $option_etu = trim($donnees[$index['option_etu'] ?? ''] ?? '');
            $niveau = trim($donnees[$index['niveau']] ?? '');

            // Validation basique
            if (empty($idUtilisateur) || empty($nom) || empty($prenom) || empty($email) || empty($filiere) || empty($niveau)) {
                $resultats['erreurs']++;
                $resultats['details'][] = "Ligne $ligne : données incomplètes";
                continue;
            }

            try {
                $ok = $this->enregistrerEtudiant($idUtilisateur, $nom, $prenom, $email, $dateNaissance, $filiere, $option_etu, $niveau);
                if ($ok) {
                    $resultats['succes']++;
                } else {
                    $resultats['erreurs']++;
                    $resultats['details'][] = "Ligne $ligne : identifiant '$idUtilisateur' déjà existant ou erreur d'insertion";
                }
            } catch (Exception $e) {
                $resultats['erreurs']++;
                $resultats['details'][] = "Ligne $ligne : " . $e->getMessage();
            }
        }

        fclose($fichier);
        return $resultats;
    }

    // Importer des professeurs depuis un fichier CSV
    public function importerProfsCSV(string $cheminFichier): array {
        $resultats = ['succes' => 0, 'erreurs' => 0, 'details' => []];

        if (!file_exists($cheminFichier)) {
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Fichier introuvable.";
            return $resultats;
        }

        $fichier = fopen($cheminFichier, 'r');
        if ($fichier === false) {
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Impossible d'ouvrir le fichier.";
            return $resultats;
        }

        // Lire l'en-tête
        $entete = fgetcsv($fichier);
        if ($entete === false) {
            fclose($fichier);
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Fichier vide ou invalide.";
            return $resultats;
        }

        // Vérifier les colonnes requises
        $colonnesRequises = ['idUtilisateur', 'nom', 'prenom', 'email', 'specialite'];
        $colonnesManquantes = array_diff($colonnesRequises, $entete);
        if (!empty($colonnesManquantes)) {
            fclose($fichier);
            $resultats['erreurs'] = 1;
            $resultats['details'][] = "Colonnes manquantes : " . implode(', ', $colonnesManquantes);
            return $resultats;
        }

        // Mapper les colonnes
        $index = array_flip($entete);

        $ligne = 1;
        while (($donnees = fgetcsv($fichier)) !== false) {
            $ligne++;
            
            // Nettoyer les données
            $idUtilisateur = trim($donnees[$index['idUtilisateur']] ?? '');
            $nom = trim($donnees[$index['nom']] ?? '');
            $prenom = trim($donnees[$index['prenom']] ?? '');
            $email = trim($donnees[$index['email']] ?? '');
            $specialite = trim($donnees[$index['specialite']] ?? '');

            // Validation basique
            if (empty($idUtilisateur) || empty($nom) || empty($prenom) || empty($email) || empty($specialite)) {
                $resultats['erreurs']++;
                $resultats['details'][] = "Ligne $ligne : données incomplètes";
                continue;
            }

            try {
                $ok = $this->enregistrerProf($idUtilisateur, $nom, $prenom, $email, $specialite);
                if ($ok) {
                    $resultats['succes']++;
                } else {
                    $resultats['erreurs']++;
                    $resultats['details'][] = "Ligne $ligne : identifiant '$idUtilisateur' déjà existant ou erreur d'insertion";
                }
            } catch (Exception $e) {
                $resultats['erreurs']++;
                $resultats['details'][] = "Ligne $ligne : " . $e->getMessage();
            }
        }

        fclose($fichier);
        return $resultats;
    }
}
