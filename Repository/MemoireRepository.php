<?php
require_once __DIR__ . '/../Config/connexion.php';

class MemoireRepository {

    public static function save(Memoire $m): bool {
        $pdo = getConnexion();
        $n   = (int)$pdo->query("SELECT COUNT(*) FROM memoire")->fetchColumn();
        $id  = 'MEM' . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
        $ok  = $pdo->prepare("
            INSERT INTO memoire (idMemoire,idCompte,idProf,theme,anneeAcademique,centre,filiere,option_mem,type,statut,fichierPdf,visible)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([$id,$m->getIdCompte(),$m->getIdProf(),$m->getTheme(),$m->getAnneeAcademique(),
                     $m->getCentre(),$m->getFiliere(),$m->getOptionMem(),$m->getType(),$m->getStatut(),
                     $m->getFichierPdf(),$m->isVisible()?1:0]);
        if ($ok) $m->setIdMemoire($id);
        return $ok;
    }

    public static function findAll(string $filiere='',string $annee='',string $centre='',string $motCle='',string $type=''): array {
        $pdo    = getConnexion();
        $sql    = "SELECT m.*,
                   u.nom AS nomEtudiant, u.prenom AS prenomEtudiant,
                   COALESCE(e.filiere, m.filiere) AS filiere,
                   p.nom AS nomProf, p.prenom AS prenomProf
                   FROM memoire m
                   JOIN compte c ON m.idCompte=c.idCompte
                   JOIN utilisateur u ON c.idUtilisateur=u.idUtilisateur
                   LEFT JOIN etudiant e ON u.idUtilisateur=e.idUtilisateur
                   JOIN utilisateur p ON m.idProf=p.idUtilisateur
                   WHERE m.statut='VALIDE' AND m.visible=1";
        $params = [];
        if ($filiere) { $sql.=" AND (e.filiere=? OR m.filiere=?)"; $params[]=$filiere; $params[]=$filiere; }
        if ($annee)   { $sql.=" AND m.anneeAcademique=?"; $params[]=$annee; }
        if ($centre)  { $sql.=" AND m.centre=?";          $params[]=$centre; }
        if ($motCle)  { $sql.=" AND (m.theme LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
                        $params[]="%$motCle%"; $params[]="%$motCle%"; $params[]="%$motCle%"; }
        if ($type)    { $sql.=" AND m.type=?"; $params[]=$type; }
        $sql.=" ORDER BY m.dateUpload DESC";
        $stmt=$pdo->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(string $id): ?array {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare("
            SELECT m.*,
                   u.nom AS nomEtudiant, u.prenom AS prenomEtudiant,
                   COALESCE(e.filiere, m.filiere) AS filiere,
                   COALESCE(e.option_etu, m.option_mem) AS option_etu,
                   e.niveau,
                   p.nom AS nomProf, p.prenom AS prenomProf,
                   pr.specialite AS specProf
            FROM memoire m
            JOIN compte c ON m.idCompte = c.idCompte
            JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
            LEFT JOIN etudiant e ON u.idUtilisateur = e.idUtilisateur
            JOIN utilisateur p ON m.idProf = p.idUtilisateur
            LEFT JOIN professeur pr ON p.idUtilisateur = pr.idUtilisateur
            WHERE m.idMemoire = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findEnAttente(string $idProf): array {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare("
            SELECT m.*, u.nom AS nomEtudiant, u.prenom AS prenomEtudiant
            FROM memoire m
            JOIN compte c ON m.idCompte=c.idCompte
            JOIN utilisateur u ON c.idUtilisateur=u.idUtilisateur
            WHERE m.idProf=? AND m.statut='EN_ATTENTE' ORDER BY m.dateUpload ASC");
        $stmt->execute([$idProf]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findEnAttenteDirection(): array {
        $pdo  = getConnexion();
        $stmt = $pdo->query("
            SELECT m.*, u.nom AS nomEtudiant, u.prenom AS prenomEtudiant,
                   p.nom AS nomProf, p.prenom AS prenomProf,
                   COALESCE(e.filiere,m.filiere) AS filiere
            FROM memoire m
            JOIN compte c ON m.idCompte=c.idCompte
            JOIN utilisateur u ON c.idUtilisateur=u.idUtilisateur
            LEFT JOIN etudiant e ON u.idUtilisateur=e.idUtilisateur
            JOIN utilisateur p ON m.idProf=p.idUtilisateur
            WHERE m.statut='EN_ATTENTE_DIRECTION' ORDER BY m.dateUpload ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatut(string $id, string $statut, ?string $commentaire=null): bool {
        $pdo    = getConnexion();
        $sql    = "UPDATE memoire SET statut=?";
        $params = [$statut];
        if ($commentaire!==null) { $sql.=", commentaireJury=?"; $params[]=$commentaire; }
        if ($statut==='VALIDE')  { $sql.=", dateValidation=NOW()"; }
        $sql.=" WHERE idMemoire=?"; $params[]=$id;
        return $pdo->prepare($sql)->execute($params);
    }

    public static function saveAncien(string $idCompte,string $idProf,string $theme,string $annee,
                                      string $centre,string $filiere,string $option,string $pdf=''): string {
        $pdo = getConnexion();
        $n   = (int)$pdo->query("SELECT COUNT(*) FROM memoire")->fetchColumn();
        $id  = 'MEM' . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
        $pdo->prepare("INSERT INTO memoire (idMemoire,idCompte,idProf,theme,anneeAcademique,centre,filiere,option_mem,type,statut,fichierPdf,dateValidation,visible)
                       VALUES (?,?,?,?,?,?,?,?,'ANCIEN','VALIDE',?,CURDATE(),1)")
            ->execute([$id,$idCompte,$idProf,$theme,$annee,$centre,$filiere,$option,$pdf]);
        return $id;
    }

    public static function supprimer(string $id): bool {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare("SELECT fichierPdf FROM memoire WHERE idMemoire=?");
        $stmt->execute([$id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['fichierPdf']) {
            $f = __DIR__.'/../uploads/memoires/'.$row['fichierPdf'];
            if (file_exists($f)) unlink($f);
        }
        foreach (['commentaire','likes','co_auteur','notification'] as $t)
            $pdo->prepare("DELETE FROM $t WHERE idMemoire=?")->execute([$id]);
        return $pdo->prepare("DELETE FROM memoire WHERE idMemoire=?")->execute([$id]);
    }

    public static function compterTotal(): int {
        return (int)getConnexion()->query("SELECT COUNT(*) FROM memoire WHERE statut='VALIDE'")->fetchColumn();
    }

    // ── CO-AUTEURS ──
    public static function ajouterCoAuteur(string $idMemoire, string $idCompte): bool {
        try {
            return getConnexion()->prepare(
                "INSERT IGNORE INTO co_auteur (idMemoire,idCompte,statut) VALUES (?,?,'EN_ATTENTE')"
            )->execute([$idMemoire,$idCompte]);
        } catch(PDOException $e){ return false; }
    }

    public static function repondreInvitation(string $idMemoire, string $idCompte, string $statut): bool {
        return getConnexion()->prepare(
            "UPDATE co_auteur SET statut=? WHERE idMemoire=? AND idCompte=?"
        )->execute([$statut,$idMemoire,$idCompte]);
    }

    public static function getCoAuteurs(string $idMemoire): array {
        $stmt = getConnexion()->prepare("
            SELECT ca.*, u.nom, u.prenom, u.idUtilisateur
            FROM co_auteur ca
            JOIN compte cp ON ca.idCompte=cp.idCompte
            JOIN utilisateur u ON cp.idUtilisateur=u.idUtilisateur
            WHERE ca.idMemoire=?");
        $stmt->execute([$idMemoire]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getInvitationsEnAttente(string $idCompte): array {
        $stmt = getConnexion()->prepare("
            SELECT ca.*, m.theme, u.nom AS nomAuteur, u.prenom AS prenomAuteur
            FROM co_auteur ca
            JOIN memoire m ON ca.idMemoire=m.idMemoire
            JOIN compte cp ON m.idCompte=cp.idCompte
            JOIN utilisateur u ON cp.idUtilisateur=u.idUtilisateur
            WHERE ca.idCompte=? AND ca.statut='EN_ATTENTE'");
        $stmt->execute([$idCompte]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── DÉLÉGATION GLOBALE ──
    public static function getDelegationActive(): ?array {
        $stmt = getConnexion()->prepare("
            SELECT dg.*, u.nom, u.prenom
            FROM delegation_globale dg
            JOIN utilisateur u ON dg.idBibliothecaire=u.idUtilisateur
            WHERE dg.active=1 ORDER BY dg.dateCreation DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function activerDelegation(string $idDE, string $idBib): bool {
        $pdo = getConnexion();
        $pdo->query("UPDATE delegation_globale SET active=0");
        return $pdo->prepare("INSERT INTO delegation_globale (idDE,idBibliothecaire,active) VALUES (?,?,1)")
                   ->execute([$idDE,$idBib]);
    }

    public static function desactiverDelegation(): bool {
        return getConnexion()->query("UPDATE delegation_globale SET active=0") !== false;
    }

    // ─── FILTRES ───
    public static function getFilieres(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT DISTINCT filiere FROM (
                SELECT filiere FROM memoire WHERE filiere IS NOT NULL AND filiere != ''
                UNION
                SELECT DISTINCT e.filiere FROM etudiant e
                JOIN compte c ON e.idUtilisateur = c.idUtilisateur
                JOIN memoire m ON m.idCompte = c.idCompte
                WHERE e.filiere IS NOT NULL AND e.filiere != ''
            ) AS all_filieres ORDER BY filiere
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getAnnees(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT DISTINCT anneeAcademique
            FROM memoire
            WHERE anneeAcademique IS NOT NULL AND anneeAcademique != ''
            ORDER BY anneeAcademique DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getCentres(): array {
        $pdo = getConnexion();
        $stmt = $pdo->query("
            SELECT DISTINCT centre
            FROM memoire
            WHERE centre IS NOT NULL AND centre != ''
            ORDER BY centre
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Nouvelle méthode pour récupérer un brouillon par invitation
    public static function getBrouillonParInvitation(string $idMemoire): ?array {
        $pdo = getConnexion();
        $stmt = $pdo->prepare("
            SELECT m.*, u.nom, u.prenom, p.nom AS nomProf, p.prenom AS prenomProf
            FROM memoire m
            JOIN compte c ON m.idCompte = c.idCompte
            JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
            JOIN utilisateur p ON m.idProf = p.idUtilisateur
            WHERE m.idMemoire = ? AND m.statut = 'BROUILLON'
        ");
        $stmt->execute([$idMemoire]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}