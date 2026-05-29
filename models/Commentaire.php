<?php
require_once __DIR__ . '/../config/database.php';

class Commentaire {
    public static function creer(int $memoireId, int $auteurId, string $contenu): int {
        $st = Database::get()->prepare(
            'INSERT INTO commentaires (memoire_id,auteur_id,contenu) VALUES (?,?,?)'
        );
        $st->execute([$memoireId, $auteurId, $contenu]);
        return (int)Database::get()->lastInsertId();
    }
    public static function parMemoire(int $memoireId): array {
        $st = Database::get()->prepare(
            'SELECT c.*, u.nom AS auteur_nom, u.role AS auteur_role
             FROM commentaires c JOIN utilisateurs u ON u.id = c.auteur_id
             WHERE c.memoire_id = ? ORDER BY c.cree_le ASC'
        );
        $st->execute([$memoireId]);
        return $st->fetchAll();
    }
}
