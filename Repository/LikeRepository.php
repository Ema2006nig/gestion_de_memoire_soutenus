<?php

require_once __DIR__ . '/../Config/connexion.php';

class LikeRepository {

    // ── Création ──
    public static function create(string $idCompte, string $idMemoire): bool {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM likes");
        $count = $stmt->fetchColumn();
        $idLike = 'LIKE' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO likes (idLike, idCompte, idMemoire, dateLike) VALUES (?, ?, ?, NOW())");
            $ok = $stmt->execute([$idLike, $idCompte, $idMemoire]);
            if ($ok) {
                $pdo->prepare("UPDATE memoire SET nbLikes = nbLikes + 1 WHERE idMemoire = ?")
                    ->execute([$idMemoire]);
            }
            return $ok;
        } catch (PDOException $e) {
            return false; // Déjà liké
        }
    }

    // ── Suppression ──
    public static function delete(string $idCompte, string $idMemoire): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("DELETE FROM likes WHERE idCompte = ? AND idMemoire = ?");
        $ok = $stmt->execute([$idCompte, $idMemoire]);
        if ($ok) {
            $pdo->prepare("UPDATE memoire SET nbLikes = GREATEST(nbLikes - 1, 0) WHERE idMemoire = ?")
                ->execute([$idMemoire]);
        }
        return $ok;
    }

    // ── Recherche ──
    public static function aLike(string $idCompte, string $idMemoire): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT idLike FROM likes WHERE idCompte = ? AND idMemoire = ?");
        $stmt->execute([$idCompte, $idMemoire]);
        return (bool)$stmt->fetch();
    }

    public static function countByMemoire(string $idMemoire): int {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE idMemoire = ?");
        $stmt->execute([$idMemoire]);
        return (int)$stmt->fetchColumn();
    }

    public static function findByMemoire(string $idMemoire): array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT l.*, u.nom, u.prenom
            FROM likes l
            JOIN compte c ON l.idCompte = c.idCompte
            JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
            WHERE l.idMemoire = ?
            ORDER BY l.dateLike DESC
        ");
        $stmt->execute([$idMemoire]);
        return $stmt->fetchAll();
    }
}
