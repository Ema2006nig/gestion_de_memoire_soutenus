<?php

require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/../Repository/ProfesseurRepository.php';
require_once __DIR__ . '/../Repository/CompteRepository.php';
require_once __DIR__ . '/../Repository/MemoireRepository.php';

class Professeur extends Utilisateur {

    private string $specialite;

    public function __construct(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $specialite,
        string $grade = '',
        string $filiere = ''
    ) {
        parent::__construct($idUtilisateur, $nom, $prenom, $email);
        $this->specialite = $specialite;
    }

    public function getSpecialite(): string         { return $this->specialite; }
    public function setSpecialite(string $s): void  { $this->specialite = $s; }

    // ── Méthodes métier (sans SQL) ──

    public function creerCompte(string $username, string $motDePasse): bool {
        $idCompte = CompteRepository::create($this->getIdUtilisateur(), $username, $motDePasse);
        return !empty($idCompte);
    }

    public function ValiderMemoire(string $idMemoire): bool {
        return MemoireRepository::updateStatut($idMemoire, 'VALIDE');
    }

    public function rejeterMemoire(string $idMemoire, string $commentaire): bool {
        return MemoireRepository::updateStatut($idMemoire, 'REJETE', $commentaire);
    }

    public function getMemoiresAValider(): array {
        return MemoireRepository::findEnAttente($this->getIdUtilisateur());
    }

    public static function trouverParId(string $id): ?Professeur {
        $data = ProfesseurRepository::findById($id);
        if (!$data) return null;
        return new Professeur(
            $data['idUtilisateur'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['specialite'] ?? ''
        );
    }

    public static function listerTous(): array {
        return ProfesseurRepository::findAll();
    }
}
