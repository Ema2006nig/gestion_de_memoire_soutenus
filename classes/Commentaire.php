<?php

require_once __DIR__ . '/../Repository/CommentaireRepository.php';

class Commentaire {

    private string $idCommentaire;
    private string $idCompte;
    private string $idMemoire;
    private string $contenu;
    private float  $note;
    private string $dateCommentaire;

    public function __construct(
        string $idCommentaire,
        string $idCompte,
        string $idMemoire,
        string $contenu,
        float  $note = 0,
        string $dateCommentaire = ''
    ) {
        $this->idCommentaire   = $idCommentaire;
        $this->idCompte        = $idCompte;
        $this->idMemoire       = $idMemoire;
        $this->contenu         = $contenu;
        $this->note            = $note;
        $this->dateCommentaire = $dateCommentaire;
    }

    public function getId(): string         { return $this->idCommentaire; }
    public function getContenu(): string    { return $this->contenu; }
    public function setContenu(string $c): void { $this->contenu = $c; }
    public function getNote(): float        { return $this->note; }
    public function setNote(float $n): void { $this->note = $n; }
    public function getDateCommentaire(): string { return $this->dateCommentaire; }

    // ── Méthodes métier (sans SQL) ──

    public function add(): bool {
        $idCommentaire = CommentaireRepository::create($this->idCompte, $this->idMemoire, $this->contenu, $this->note);
        return !empty($idCommentaire);
    }
}



require_once __DIR__ . '/../Repository/LikeRepository.php';

class Like {

    private string $idLike;
    private string $idCompte;
    private string $idMemoire;
    private string $dateLike;

    public function __construct(
        string $idLike,
        string $idCompte,
        string $idMemoire,
        string $dateLike = ''
    ) {
        $this->idLike    = $idLike;
        $this->idCompte  = $idCompte;
        $this->idMemoire = $idMemoire;
        $this->dateLike  = $dateLike;
    }

    public function getId(): string         { return $this->idLike; }
    public function getIdMemoire(): string  { return $this->idMemoire; }
    public function getDateLike(): string   { return $this->dateLike; }

    // ── Méthodes métier (sans SQL) ──

    public function add(): bool {
        return LikeRepository::create($this->idCompte, $this->idMemoire);
    }

    public function remove(): bool {
        return LikeRepository::delete($this->idCompte, $this->idMemoire);
    }

    public static function aLike(string $idCompte, string $idMemoire): bool {
        return LikeRepository::aLike($idCompte, $idMemoire);
    }
}
