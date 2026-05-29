<?php
require_once __DIR__ . '/../config/database.php';

class Utilisateur {
    public static function parEmail(string $email): ?array {
        $st = Database::get()->prepare('SELECT * FROM utilisateurs WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $u = $st->fetch();
        return $u ?: null;
    }
    public static function parId(int $id): ?array {
        $st = Database::get()->prepare('SELECT * FROM utilisateurs WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public static function tous(): array {
        return Database::get()->query('SELECT * FROM utilisateurs ORDER BY nom')->fetchAll();
    }
    public static function parRole(string $role): array {
        $st = Database::get()->prepare('SELECT * FROM utilisateurs WHERE role = ? AND actif = 1');
        $st->execute([$role]);
        return $st->fetchAll();
    }
    public static function creer(array $d): int {
        $st = Database::get()->prepare(
            'INSERT INTO utilisateurs (nom,email,mot_de_passe,role,matricule,grade) VALUES (?,?,?,?,?,?)'
        );
        $st->execute([
            $d['nom'], $d['email'],
            password_hash($d['mot_de_passe'], PASSWORD_DEFAULT),
            $d['role'], $d['matricule'] ?? null, $d['grade'] ?? null
        ]);
        return (int)Database::get()->lastInsertId();
    }
    public static function basculerActif(int $id): void {
        Database::get()->prepare('UPDATE utilisateurs SET actif = 1 - actif WHERE id = ?')->execute([$id]);
    }
}
