<?php

require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/../Repository/EtudiantRepository.php';
require_once __DIR__ . '/../Repository/CompteRepository.php';
require_once __DIR__ . '/../Repository/MemoireRepository.php';

class Etudiant extends Utilisateur {

    private string  $dateNaissance;
    private string  $filiere;
    private string  $option_etu;
    private string  $niveau;
    private bool    $peutUploader;

    // Constructeur
    public function __construct(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $dateNaissance,
        string $filiere,
        string $option_etu,
        string $niveau,
        bool   $peutUploader = false
    ) {
        parent::__construct($idUtilisateur, $nom, $prenom, $email);
        $this->dateNaissance = $dateNaissance;
        $this->filiere      = $filiere;
        $this->option_etu   = $option_etu;
        $this->niveau       = $niveau;
        $this->peutUploader = $peutUploader;
    }

    // ── Getters / Setters ──
    public function getDateNaissance(): string      { return $this->dateNaissance; }
    public function setDateNaissance(string $d): void { $this->dateNaissance = $d; }

    public function getFiliere(): string      { return $this->filiere; }
    public function setFiliere(string $f): void { $this->filiere = $f; }

    public function getOption(): string       { return $this->option_etu; }
    public function setOption(string $o): void  { $this->option_etu = $o; }

    public function getNiveau(): string       { return $this->niveau; }
    public function setNiveau(string $n): void  { $this->niveau = $n; }

    public function isPeutUploader(): bool    { return $this->peutUploader; }
    public function setPeutUploader(bool $b): void { $this->peutUploader = $b; }

    // ── Méthodes métier (sans SQL) ──

    // Créer un compte après vérification de l'identifiant
    public function creerCompte(string $username, string $motDePasse): bool {
        $idCompte = CompteRepository::create($this->getIdUtilisateur(), $username, $motDePasse);
        return !empty($idCompte);
    }

    // Soumettre un mémoire
    public function soumettreMemoire(Memoire $memoire): bool {
        return $memoire->save();
    }

    // Récupérer les mémoires de l'étudiant
    public function getMemoires(): array {
        $compte = CompteRepository::trouverParUtilisateur($this->getIdUtilisateur());
        if (!$compte) return [];
        return MemoireRepository::findByEtudiant($compte['idCompte']);
    }

    // Rechercher un mémoire
    public static function rechercherMemoire(
        string $filiere = '',
        string $annee = '',
        string $centre = '',
        string $motCle = ''
    ): array {
        return MemoireRepository::findAll($filiere, $annee, $centre, $motCle);
    }

    // Trouver un étudiant par idUtilisateur
    public static function trouverParId(string $id): ?Etudiant {
        $data = EtudiantRepository::findById($id);
        if (!$data) return null;
        return new Etudiant(
            $data['idUtilisateur'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['dateNaissance'] ?? '',
            $data['filiere'] ?? '',
            $data['option_etu'] ?? '',
            $data['niveau'] ?? '',
            (bool)($data['peutUploader'] ?? 0)
        );
    }
}
