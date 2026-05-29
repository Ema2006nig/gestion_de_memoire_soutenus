<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/../core/Helpers.php';

class Notification {
    public static function envoyer(int $userId, string $message, string $sujetMail, string $corpsMail): void {
        $st = Database::get()->prepare(
            'INSERT INTO notifications (utilisateur_id,message) VALUES (?,?)'
        );
        $st->execute([$userId, $message]);
        $u = Utilisateur::parId($userId);
        if ($u && !empty($u['email'])) {
            send_mail($u['email'], $sujetMail, $corpsMail);
        }
    }
    public static function pourUtilisateur(int $userId): array {
        $st = Database::get()->prepare(
            'SELECT * FROM notifications WHERE utilisateur_id = ? ORDER BY cree_le DESC LIMIT 20'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }
    public static function marquerLues(int $userId): void {
        Database::get()->prepare('UPDATE notifications SET lu = 1 WHERE utilisateur_id = ?')->execute([$userId]);
    }
}
