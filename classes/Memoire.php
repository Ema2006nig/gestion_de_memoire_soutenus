<?php
require_once __DIR__ . '/../Repository/MemoireRepository.php';

class Memoire {
    private string $idMemoire;
    private string $idCompte;
    private string $idProf;
    private string $theme;
    private string $anneeAcademique;
    private string $centre;
    private string $filiere;
    private string $option_mem;
    private string $type;
    private string $statut;
    private string $fichierPdf;
    private bool   $visible;

    public function __construct(string $idMemoire,string $idCompte,string $idProf,string $theme,
                                string $anneeAcademique,string $centre,string $type='NOUVEAU',
                                string $statut='EN_ATTENTE',string $fichierPdf='',bool $visible=true,
                                string $filiere='',string $option_mem='') {
        $this->idMemoire=$idMemoire; $this->idCompte=$idCompte; $this->idProf=$idProf;
        $this->theme=$theme; $this->anneeAcademique=$anneeAcademique; $this->centre=$centre;
        $this->type=$type; $this->statut=$statut; $this->fichierPdf=$fichierPdf;
        $this->visible=$visible; $this->filiere=$filiere; $this->option_mem=$option_mem;
    }

    public function getIdMemoire(): string     { return $this->idMemoire; }
    public function setIdMemoire(string $id)   { $this->idMemoire=$id; }
    public function getIdCompte(): string      { return $this->idCompte; }
    public function getIdProf(): string        { return $this->idProf; }
    public function getTheme(): string         { return $this->theme; }
    public function getAnneeAcademique(): string { return $this->anneeAcademique; }
    public function getCentre(): string        { return $this->centre; }
    public function getFiliere(): string       { return $this->filiere; }
    public function getOptionMem(): string     { return $this->option_mem; }
    public function getType(): string          { return $this->type; }
    public function getStatut(): string        { return $this->statut; }
    public function getFichierPdf(): string    { return $this->fichierPdf; }
    public function isVisible(): bool          { return $this->visible; }
    public function save(): bool               { return MemoireRepository::save($this); }
}
