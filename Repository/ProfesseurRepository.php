<?php

require_once __DIR__ . '/../Config/connexion.php';

class ProfesseurRepository {

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
        
        // Insérer dans professeur (table spécifique)
        $stmt = $pdo->prepare("
            INSERT INTO professeur (idUtilisateur, specialite)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $data['idUtilisateur'],
            $data['specialite'] ?? null
        ]);
        
        return $data['idUtilisateur'];
    }

    // ── Recherche par ID (avec jointure) ──
    public static function findById(string $id): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT u.*, p.specialite
            FROM utilisateur u
            JOIN professeur p ON u.idUtilisateur = p.idUtilisateur
            WHERE u.idUtilisateur = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ── Liste tous les professeurs ──
    public static function findAll(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT u.*, p.specialite
            FROM utilisateur u
            JOIN professeur p ON u.idUtilisateur = p.idUtilisateur
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
        
        // Mettre à jour professeur
        if (isset($data['specialite'])) {
            $stmt = $pdo->prepare("UPDATE professeur SET specialite = ? WHERE idUtilisateur = ?");
            $result = $result && $stmt->execute([$data['specialite'], $id]);
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
