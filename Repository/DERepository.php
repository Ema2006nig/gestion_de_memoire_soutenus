<?php

require_once __DIR__ . '/../Config/connexion.php';

class DERepository {

    // ── Création ──
    public static function create(array $data): string {
        $pdo = getConnexion();
        
        // Insérer dans utilisateur (table de base)
        $stmt = $pdo->prepare("
            INSERT INTO utilisateur (idUtilisateur, nom, prenom, email)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['idUtilisateur'],
            $data['nom'],
            $data['prenom'],
            $data['email']
        ]);
        
        // Insérer dans de (table spécifique)
        $stmt = $pdo->prepare("
            INSERT INTO de (idUtilisateur, bureau)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $data['idUtilisateur'],
            $data['bureau'] ?? null
        ]);
        
        return $data['idUtilisateur'];
    }

    // ── Recherche par ID (avec jointure) ──
    public static function findById(string $id): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT u.*, d.bureau
            FROM utilisateur u
            JOIN de d ON u.idUtilisateur = d.idUtilisateur
            WHERE u.idUtilisateur = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ── Liste tous les DE ──
    public static function findAll(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT u.*, d.bureau
            FROM utilisateur u
            JOIN de d ON u.idUtilisateur = d.idUtilisateur
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll();
    }

    // ── Modification ──
    public static function update(string $id, array $data): bool {
        $pdo = getConnexion();
        
        // Mettre à jour utilisateur
        $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?";
        $params = [$data['nom'], $data['prenom'], $data['email']];
        
        $sql .= " WHERE idUtilisateur = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        // Mettre à jour de
        if (isset($data['bureau'])) {
            $stmt = $pdo->prepare("UPDATE de SET bureau = ? WHERE idUtilisateur = ?");
            $result = $result && $stmt->execute([$data['bureau'], $id]);
        }
        
        return $result;
    }

    // ── Suppression ──
    public static function delete(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE idUtilisateur = ?");
        return $stmt->execute([$id]);
    }
}
