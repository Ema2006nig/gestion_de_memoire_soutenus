<?php

require_once __DIR__ . '/../Config/connexion.php';

class Compte {

    private string $idCompte;
    private string $idUtilisateur;
    private string $username;
    private string $motDePasse;
    private string $dateCreation;

    // Composition : le compte contient les mémoires soumis
    private array $memoires = [];

    public function __construct(
        string $idCompte,
        string $idUtilisateur,
        string $username,
        string $motDePasse,
        string $dateCreation = ''
    ) {
        $this->idCompte       = $idCompte;
        $this->idUtilisateur  = $idUtilisateur;
        $this->username       = $username;
        $this->motDePasse     = $motDePasse;
        $this->dateCreation   = $dateCreation;
    }

    // ── Getters / Setters ──
    public function getIdCompte(): string         { return $this->idCompte; }
    public function getIdUtilisateur(): string    { return $this->idUtilisateur; }
    public function getUsername(): string         { return $this->username; }
    public function setUsername(string $u): void  { $this->username = $u; }
    public function getDateCreation(): string     { return $this->dateCreation; }

    // ── Méthodes métier (sans SQL) ──
    // Les méthodes de persistance sont déplacées dans CompteRepository
}
