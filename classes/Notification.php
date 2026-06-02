<?php

require_once __DIR__ . '/../Config/connexion.php';
require_once __DIR__ . '/../Repository/NotificationRepository.php';

class Notification {

    private string $idNotif;
    private string $idCompte;
    private string $message;
    private bool   $lu;
    private string $typeNotif;
    private string $dateNotif;
    private ?string $idMemoire;

    public function __construct(
        string $idNotif,
        string $idCompte,
        string $message,
        bool   $lu       = false,
        string $typeNotif = 'INFO',
        string $dateNotif = '',
        ?string $idMemoire = null
    ) {
        $this->idNotif   = $idNotif;
        $this->idCompte  = $idCompte;
        $this->message   = $message;
        $this->lu        = $lu;
        $this->typeNotif = $typeNotif;
        $this->dateNotif = $dateNotif;
        $this->idMemoire = $idMemoire;
    }

    public function getId(): string          { return $this->idNotif; }
    public function getIdCompte(): string    { return $this->idCompte; }
    public function getMessage(): string     { return $this->message; }
    public function setMessage(string $m): void { $this->message = $m; }
    public function isLu(): bool             { return $this->lu; }
    public function setLu(bool $b): void     { $this->lu = $b; }
    public function getTypeNotif(): string   { return $this->typeNotif; }
    public function getDateNotif(): string   { return $this->dateNotif; }

    // ── Méthodes métier (sans SQL) ──
    
    public static function compterNonLus(string $idCompte): int {
        return NotificationRepository::compterNonLus($idCompte);
    }

    public static function getByCompte(string $idCompte): array {
        return NotificationRepository::getByCompte($idCompte);
    }

    public static function marquerToutLu(string $idCompte): bool {
        return NotificationRepository::marquerToutLu($idCompte);
    }
}
