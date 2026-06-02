<?php

require_once __DIR__ . '/../Config/connexion.php';

class EtudiantRepository {

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
        
        // Insérer dans etudiant (table spécifique)
        $stmt = $pdo->prepare("
            INSERT INTO etudiant (idUtilisateur, dateNaissance, filiere, option_etu, niveau, peutUploader)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['idUtilisateur'],
            $data['dateNaissance'] ?? null,
            $data['filiere'] ?? null,
            $data['option_etu'] ?? null,
            $data['niveau'] ?? null,
            $data['peutUploader'] ?? 0
        ]);
        
        return $data['idUtilisateur'];
    }

    // ── Recherche par ID (avec jointure) ──
    public static function findById(string $id): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT u.*, e.dateNaissance, e.filiere, e.option_etu, e.niveau, e.peutUploader
            FROM utilisateur u
            JOIN etudiant e ON u.idUtilisateur = e.idUtilisateur
            WHERE u.idUtilisateur = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ── Liste tous les étudiants ──
    public static function findAll(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT u.*, e.dateNaissance, e.filiere, e.option_etu, e.niveau, e.peutUploader
            FROM utilisateur u
            JOIN etudiant e ON u.idUtilisateur = e.idUtilisateur
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll();
    }

    // ── Liste avec compte ──
    public static function listerAvecCompte(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT u.*, e.dateNaissance, e.filiere, e.option_etu, e.niveau, e.peutUploader, c.idCompte, c.username
            FROM utilisateur u
            JOIN etudiant e ON u.idUtilisateur = e.idUtilisateur
            LEFT JOIN compte c ON u.idUtilisateur = c.idUtilisateur
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
        
        if (isset($data['dateNaissance'])) {
            $sql .= ", dateNaissance = ?";
            $params[] = $data['dateNaissance'];
        }
        
        $sql .= " WHERE idUtilisateur = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        // Mettre à jour etudiant
        $sql = "UPDATE etudiant SET filiere = ?, option_etu = ?, niveau = ?, peutUploader = ?";
        $params = [
            $data['filiere'] ?? null,
            $data['option_etu'] ?? null,
            $data['niveau'] ?? null,
            $data['peutUploader'] ?? 0
        ];
        
        if (isset($data['dateNaissance'])) {
            $sql .= ", dateNaissance = ?";
            $params[] = $data['dateNaissance'];
        }
        
        $sql .= " WHERE idUtilisateur = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        return $result && $stmt->execute($params);
    }

    // ── Suppression ──
    public static function delete(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE idUtilisateur = ?");
        return $stmt->execute([$id]);
    }

    // ── Activation de l'upload ──
    public static function activerUpload(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("UPDATE etudiant SET peutUploader = 1 WHERE idUtilisateur = ?");
        return $stmt->execute([$id]);
    }

    // ── Désactivation de l'upload ──
    public static function desactiverUpload(string $id): bool {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("UPDATE etudiant SET peutUploader = 0 WHERE idUtilisateur = ?");
        return $stmt->execute([$id]);
    }

    // ── Liste distinctes pour filtres ──
    public static function getFilieres(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT DISTINCT filiere FROM etudiant WHERE filiere IS NOT NULL ORDER BY filiere");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getNiveaux(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT DISTINCT niveau FROM etudiant WHERE niveau IS NOT NULL ORDER BY niveau");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
