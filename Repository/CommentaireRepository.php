<?php

require_once __DIR__ . '/../Config/connexion.php';

class CommentaireRepository {

    public static function create(string $idCompte, string $idMemoire, string $contenu, float $note = 0): string {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM commentaire");
        $count = $stmt->fetchColumn();
        $idCommentaire = 'COM' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO commentaire (idCommentaire, idCompte, idMemoire, contenu, note, dateCommentaire)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$idCommentaire, $idCompte, $idMemoire, $contenu, $note]);
        
        return $idCommentaire;
    }

    public static function delete(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("DELETE FROM commentaire WHERE idCommentaire = ?");
        return $stmt->execute([$id]);
    }

    // Version corrigée : jointure simple sur utilisateur, sans LEFT JOIN sur les tables spécifiques
    public static function getByMemoire(string $idMemoire): array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT co.*, u.nom, u.prenom, u.role
            FROM commentaire co
            JOIN compte c ON co.idCompte = c.idCompte
            JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
            WHERE co.idMemoire = ?
            ORDER BY co.dateCommentaire DESC
        ");
        $stmt->execute([$idMemoire]);
        return $stmt->fetchAll();
    }

    public static function findById(string $id): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM commentaire WHERE idCommentaire = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function countByMemoire(string $idMemoire): int {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire WHERE idMemoire = ?");
        $stmt->execute([$idMemoire]);
        return (int)$stmt->fetchColumn();
    }
}