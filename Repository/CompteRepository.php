<?php
require_once __DIR__ . '/../Config/connexion.php';

/**
 * Accès aux comptes utilisateur.
 *
 * Authentification :
 *  - identifiant = adresse e-mail (champ utilisateur.email)
 *  - mot de passe hashé via password_hash()
 *
 * La colonne "compte.username" est conservée pour la rétro-compatibilité
 * mais elle stocke désormais l'e-mail (utilisé comme identifiant unique).
 */
class CompteRepository
{
    /* ---------------------------------------------------------------- */
    /*  Lecture                                                         */
    /* ---------------------------------------------------------------- */

    /**
     * Vérifie un couple (email, motDePasse) et retourne le compte enrichi
     * des informations utilisateur + rôle. Renvoie null si l'identification
     * échoue.
     */
    public static function login(string $email, string $motDePasse): ?array
    {
        $stmt = getConnexion()->prepare(<<<SQL
            SELECT c.*, u.idUtilisateur, u.nom, u.prenom, u.email, u.role,
                   e.filiere, e.option_etu, e.niveau, e.peutUploader,
                   p.specialite, p.grade,
                   d.bureau
              FROM compte c
              JOIN utilisateur u  ON u.idUtilisateur = c.idUtilisateur
         LEFT JOIN etudiant e     ON e.idUtilisateur = u.idUtilisateur
         LEFT JOIN professeur p   ON p.idUtilisateur = u.idUtilisateur
         LEFT JOIN de d           ON d.idUtilisateur = u.idUtilisateur
             WHERE u.email = ?
             LIMIT 1
        SQL);
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data || !password_verify($motDePasse, $data['motDePasse'])) {
            return null;
        }
        return $data;
    }

    /** Cherche un utilisateur pré-enregistré par e-mail. */
    public static function trouverUtilisateurParEmail(string $email): ?array
    {
        $stmt = getConnexion()->prepare("SELECT * FROM utilisateur WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Cherche un utilisateur pré-enregistré par identifiant (matricule). */
    public static function verifierIdentifiant(string $id): ?array
    {
        $stmt = getConnexion()->prepare("SELECT * FROM utilisateur WHERE idUtilisateur = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Le compte est-il déjà activé pour cet utilisateur ? */
    public static function compteExiste(string $idUtilisateur): bool
    {
        $stmt = getConnexion()->prepare("SELECT 1 FROM compte WHERE idUtilisateur = ? LIMIT 1");
        $stmt->execute([$idUtilisateur]);
        return (bool) $stmt->fetchColumn();
    }

    public static function findById(string $idCompte): ?array
    {
        $stmt = getConnexion()->prepare(<<<SQL
            SELECT c.*, u.nom, u.prenom, u.email, u.role
              FROM compte c
              JOIN utilisateur u ON u.idUtilisateur = c.idUtilisateur
             WHERE c.idCompte = ?
        SQL);
        $stmt->execute([$idCompte]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ---------------------------------------------------------------- */
    /*  Écriture                                                        */
    /* ---------------------------------------------------------------- */

    /**
     * Crée un compte. L'identifiant de connexion (username) stocké en base
     * est volontairement égal à l'e-mail pour rester cohérent avec le
     * nouveau workflow.
     */
    public static function create(string $idUtilisateur, string $username, string $motDePasse): string
    {
        $pdo   = getConnexion();
        $count = (int) $pdo->query("SELECT COUNT(*) FROM compte")->fetchColumn();
        $idCpt = 'CPT' . str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        $hash  = password_hash($motDePasse, PASSWORD_DEFAULT);

        $pdo->prepare("INSERT INTO compte (idCompte, idUtilisateur, username, motDePasse) VALUES (?, ?, ?, ?)")
            ->execute([$idCpt, $idUtilisateur, $username, $hash]);

        return $idCpt;
    }

    public static function changerMotDePasse(string $idCompte, string $nouveau): bool
    {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        return getConnexion()
            ->prepare("UPDATE compte SET motDePasse = ? WHERE idCompte = ?")
            ->execute([$hash, $idCompte]);
    }
}
