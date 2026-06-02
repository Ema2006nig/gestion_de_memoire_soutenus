<?php

require_once __DIR__ . '/../Config/connexion.php';

class UtilisateurRepository {

    // ── Recherche ──
    public static function findAll(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT * FROM utilisateur ORDER BY nom, prenom");
        return $stmt->fetchAll();
    }

    public static function findById(string $id): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE idUtilisateur = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByRole(string $role): array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE role = ? ORDER BY nom, prenom");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public static function findByFiliere(string $filiere): array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE filiere = ? ORDER BY nom, prenom");
        $stmt->execute([$filiere]);
        return $stmt->fetchAll();
    }

    // ── Création ──
    public static function create(array $data): string {
        $pdo = getConnexion();
        
        $stmt = $pdo->prepare("
            INSERT INTO utilisateur 
            (idUtilisateur, nom, prenom, email, role, filiere, niveau, peutUploader, specialite, bureau, dateNaissance, option_etu)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['idUtilisateur'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['role'],
            $data['filiere'] ?? null,
            $data['niveau'] ?? null,
            $data['peutUploader'] ?? 0,
            $data['specialite'] ?? null,
            $data['bureau'] ?? null,
            $data['dateNaissance'] ?? null,
            $data['option_etu'] ?? null
        ]);
        
        return $data['idUtilisateur'];
    }

    // ── Modification ──
    public static function update(string $id, array $data): bool {
        $pdo = getConnexion();
        
        $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?";
        $params = [$data['nom'], $data['prenom'], $data['email']];
        
        if (isset($data['filiere'])) {
            $sql .= ", filiere = ?";
            $params[] = $data['filiere'];
        }
        if (isset($data['niveau'])) {
            $sql .= ", niveau = ?";
            $params[] = $data['niveau'];
        }
        if (isset($data['specialite'])) {
            $sql .= ", specialite = ?";
            $params[] = $data['specialite'];
        }
        if (isset($data['bureau'])) {
            $sql .= ", bureau = ?";
            $params[] = $data['bureau'];
        }
        
        $sql .= " WHERE idUtilisateur = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // ── Suppression ──
    public static function delete(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE idUtilisateur = ?");
        return $stmt->execute([$id]);
    }

    // ── Statistiques ──
    public static function countByRole(string $role): int {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE role = ?");
        $stmt->execute([$role]);
        return (int)$stmt->fetchColumn();
    }

    public static function countTotal(): int {
        $pdo = getConnexion();
        return (int)$pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
    }

    // ── Liste distinctes pour filtres ──
    public static function getFilieres(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT DISTINCT filiere FROM utilisateur WHERE filiere IS NOT NULL ORDER BY filiere");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getNiveaux(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT DISTINCT niveau FROM utilisateur WHERE niveau IS NOT NULL ORDER BY niveau");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── Méthodes pour le DE ──
    public static function listerEtudiantsAvecCompte(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT u.*, c.idCompte, c.username
            FROM utilisateur u
            LEFT JOIN compte c ON u.idUtilisateur = c.idUtilisateur
            WHERE u.role IN ('ETUDIANT','ETU_CONSULTANT')
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll();
    }

    public static function listerProfs(): array {
        return self::findByRole('PROFESSEUR');
    }
}
