<?php

require_once __DIR__ . '/../Config/connexion.php';

class NotificationRepository {

    // ── Création ──
    public static function create(string $idCompte, string $message, string $type, ?string $idMemoire = null): string {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM notification");
        $count = $stmt->fetchColumn();
        $idNotif = 'NOTIF' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO notification (idNotif, idCompte, message, lu, typeNotif, dateNotif, idMemoire)
            VALUES (?, ?, ?, 0, ?, NOW(), ?)
        ");
        $stmt->execute([$idNotif, $idCompte, $message, $type, $idMemoire]);
        
        return $idNotif;
    }

    // ── Modification ──
    public static function marquerLu(string $idNotif): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("UPDATE notification SET lu = 1 WHERE idNotif = ?");
        return $stmt->execute([$idNotif]);
    }

    public static function marquerToutLu(string $idCompte): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("UPDATE notification SET lu = 1 WHERE idCompte = ?");
        return $stmt->execute([$idCompte]);
    }

    // ── Recherche ──
    public static function getByCompte(string $idCompte): array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT * FROM notification 
            WHERE idCompte = ? 
            ORDER BY dateNotif DESC
        ");
        $stmt->execute([$idCompte]);
        return $stmt->fetchAll();
    }

    public static function findById(string $id): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM notification WHERE idNotif = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ── Statistiques ──
    public static function compterNonLus(string $idCompte): int {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE idCompte = ? AND lu = 0");
        $stmt->execute([$idCompte]);
        return (int)$stmt->fetchColumn();
    }

    public static function compterTotal(string $idCompte): int {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE idCompte = ?");
        $stmt->execute([$idCompte]);
        return (int)$stmt->fetchColumn();
    }

    // ── Suppression ──
    public static function delete(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("DELETE FROM notification WHERE idNotif = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteOlderThan(string $idCompte, int $days): int {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            DELETE FROM notification 
            WHERE idCompte = ? AND dateNotif < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$idCompte, $days]);
        return $stmt->rowCount();
    }
}
