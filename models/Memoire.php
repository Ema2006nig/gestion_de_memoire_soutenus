<?php
require_once __DIR__ . '/../config/database.php';

class Memoire {
    public static function creer(array $d): int {
        $st = Database::get()->prepare(
            'INSERT INTO memoires (etudiant_id,professeur_id,titre,resume,fichier) VALUES (?,?,?,?,?)'
        );
        $st->execute([$d['etudiant_id'], $d['professeur_id'], $d['titre'], $d['resume'], $d['fichier']]);
        return (int)Database::get()->lastInsertId();
    }
    public static function parId(int $id): ?array {
        $st = Database::get()->prepare(
            'SELECT m.*, e.nom AS etudiant_nom, p.nom AS prof_nom
             FROM memoires m
             JOIN utilisateurs e ON e.id = m.etudiant_id
             LEFT JOIN utilisateurs p ON p.id = m.professeur_id
             WHERE m.id = ?'
        );
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public static function parEtudiant(int $eid): array {
        $st = Database::get()->prepare('SELECT * FROM memoires WHERE etudiant_id = ? ORDER BY date_soumission DESC');
        $st->execute([$eid]);
        return $st->fetchAll();
    }
    public static function parProfesseur(int $pid): array {
        $st = Database::get()->prepare(
            'SELECT m.*, e.nom AS etudiant_nom FROM memoires m
             JOIN utilisateurs e ON e.id = m.etudiant_id
             WHERE m.professeur_id = ? ORDER BY date_soumission DESC'
        );
        $st->execute([$pid]);
        return $st->fetchAll();
    }
    public static function tous(?string $recherche = null): array {
        $sql = 'SELECT m.*, e.nom AS etudiant_nom FROM memoires m
                JOIN utilisateurs e ON e.id = m.etudiant_id';
        $p = [];
        if ($recherche) { $sql .= ' WHERE m.titre LIKE ?'; $p[] = '%' . $recherche . '%'; }
        $sql .= ' ORDER BY date_soumission DESC';
        $st = Database::get()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }
    public static function enLigne(?string $recherche = null): array {
        $sql = 'SELECT m.*, e.nom AS etudiant_nom FROM memoires m
                JOIN utilisateurs e ON e.id = m.etudiant_id
                WHERE m.statut = "en_ligne"';
        $p = [];
        if ($recherche) { $sql .= ' AND m.titre LIKE ?'; $p[] = '%' . $recherche . '%'; }
        $st = Database::get()->prepare($sql . ' ORDER BY date_soumission DESC');
        $st->execute($p);
        return $st->fetchAll();
    }
    public static function changerStatut(int $id, string $statut): void {
        $st = Database::get()->prepare('UPDATE memoires SET statut = ? WHERE id = ?');
        $st->execute([$statut, $id]);
    }
    public static function statistiques(): array {
        $r = Database::get()->query(
            'SELECT statut, COUNT(*) AS n FROM memoires GROUP BY statut'
        )->fetchAll();
        $out = ['soumis'=>0,'valide_prof'=>0,'refuse_prof'=>0,'valide_direction'=>0,'en_ligne'=>0,'total'=>0];
        foreach ($r as $row) { $out[$row['statut']] = (int)$row['n']; $out['total'] += (int)$row['n']; }
        return $out;
    }
}
