<?php

abstract class Utilisateur {

    // Attributs privés
    private string $idUtilisateur;
    private string $nom;
    private string $prenom;
    private string $email;

    // Constructeur
    public function __construct(
        string $idUtilisateur,
        string $nom,
        string $prenom,
        string $email
    ) {
        $this->idUtilisateur = $idUtilisateur;
        $this->nom           = $nom;
        $this->prenom        = $prenom;
        $this->email         = $email;
    }

    // ── Getters ──
    public function getIdUtilisateur(): string { return $this->idUtilisateur; }
    public function getNom(): string           { return $this->nom; }
    public function getPrenom(): string        { return $this->prenom; }
    public function getEmail(): string         { return $this->email; }
    public function getNomComplet(): string    { return $this->prenom . ' ' . $this->nom; }

    // ── Setters ──
    public function setNom(string $nom): void       { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void   { $this->email = $email; }
}
