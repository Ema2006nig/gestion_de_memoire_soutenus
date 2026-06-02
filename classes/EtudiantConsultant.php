<?php

require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/../Config/connexion.php';

class EtudiantConsultant extends Utilisateur {

    private string $dateNaissance;
    private string $filiere;
    private string $option_etu;
    private string $niveau;

    public function __construct(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email,
        string $dateNaissance,
        string $filiere,
        string $option_etu,
        string $niveau
    ) {
        parent::__construct($idUtilisateur, $nom, $prenom, $email);
        $this->dateNaissance = $dateNaissance;
        $this->filiere      = $filiere;
        $this->option_etu   = $option_etu;
        $this->niveau       = $niveau;
    }

    public function getDateNaissance(): string      { return $this->dateNaissance; }
    public function setDateNaissance(string $d): void { $this->dateNaissance = $d; }

    public function getFiliere(): string         { return $this->filiere; }
    public function setFiliere(string $f): void  { $this->filiere = $f; }
    public function getOption(): string          { return $this->option_etu; }
    public function setOption(string $o): void   { $this->option_etu = $o; }
    public function getNiveau(): string            { return $this->niveau; }
    public function setNiveau(string $n): void     { $this->niveau = $n; }

    public function creerCompte(string $username, string $motDePasse): bool {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM compte");
        $count = $stmt->fetchColumn();
        $idCompte = 'CPT' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO compte (idCompte, idUtilisateur, username, motDePasse) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$idCompte, $this->getIdUtilisateur(), $username, $hash]);
    }

    public static function trouverParId(string $id): ?EtudiantConsultant {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE idUtilisateur = ? AND role = 'ETU_CONSULTANT'");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if (!$data) return null;
        return new EtudiantConsultant(
            $data['idUtilisateur'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['dateNaissance'] ?? '',
            $data['filiere'] ?? '',
            $data['option_etu'] ?? '',
            $data['niveau'] ?? ''
        );
    }
}
