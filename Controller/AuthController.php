<?php
/**
 * AuthController — inscription, connexion, déconnexion, changement de mot de passe.
 *
 * L'authentification se fait par e-mail + mot de passe.
 * La logique métier (rôles, profils spécifiques) est conservée à l'identique.
 */

require_once __DIR__ . '/../classes/Compte.php';
require_once __DIR__ . '/../classes/Etudiant.php';
require_once __DIR__ . '/../classes/Professeur.php';
require_once __DIR__ . '/../classes/DE.php';
require_once __DIR__ . '/../Repository/CompteRepository.php';
require_once __DIR__ . '/../Config/connexion.php';

class AuthController
{
    /* ================================================================
     *  INSCRIPTION
     * ================================================================ */

    public static function register(): void
    {
        if (!isset($_POST['btn_register'])) return;

        $email        = trim($_POST['email']        ?? '');
        $motDePasse   = trim($_POST['motDePasse']   ?? '');
        $confirmation = trim($_POST['confirmer']    ?? '');

        $_SESSION['form_register'] = ['email' => $email];

        // ── Validations ────────────────────────────────────────────────
        if ($email === '' || $motDePasse === '') {
            self::backRegister("Tous les champs sont obligatoires.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::backRegister("Adresse e-mail invalide.");
        }
        if (strlen($motDePasse) < 6) {
            self::backRegister("Le mot de passe doit contenir au moins 6 caractères.");
        }
        if ($motDePasse !== $confirmation) {
            self::backRegister("Les deux mots de passe ne correspondent pas.");
        }

        // ── L'e-mail doit avoir été pré-enregistré par la Direction ────
        $utilisateur = CompteRepository::trouverUtilisateurParEmail($email);
        if (!$utilisateur) {
            self::backRegister("Cet e-mail n'est pas reconnu. Contactez la Direction des Études.");
        }

        if (CompteRepository::compteExiste($utilisateur['idUtilisateur'])) {
            self::backRegister("Un compte existe déjà pour cet e-mail. Connectez-vous.");
        }

        // ── Création effective ─────────────────────────────────────────
        try {
            $ok = self::creerCompteSelonRole($utilisateur, $email, $motDePasse);
        } catch (PDOException $e) {
            self::backRegister("Erreur technique pendant la création du compte. Réessayez.");
            return;
        }

        if (!$ok) {
            self::backRegister("Impossible de créer le compte. Vérifiez vos informations.");
        }

        unset($_SESSION['form_register']);
        $_SESSION['succes'] = "Compte activé. Vous pouvez maintenant vous connecter.";
        header('Location: ../View/auth/login.php');
        exit;
    }

    /**
     * Crée le compte avec la classe métier adaptée au rôle de l'utilisateur.
     * Le rôle et les attributs spécifiques sont lus depuis la table dédiée
     * (etudiant, professeur, de, bibliothecaire).
     */
    private static function creerCompteSelonRole(array $utilisateur, string $email, string $motDePasse): bool
    {
        $details = self::chargerDetailsRole($utilisateur['idUtilisateur']);
        $data    = array_merge($utilisateur, $details['data']);
        $role    = $details['role'];

        switch ($role) {
            case 'ETUDIANT':
                $e = new Etudiant(
                    $data['idUtilisateur'], $data['nom'], $data['prenom'], $data['email'],
                    $data['dateNaissance'] ?? '',
                    $data['filiere']       ?? '',
                    $data['option_etu']    ?? '',
                    $data['niveau']        ?? '',
                    (bool) ($data['peutUploader'] ?? false)
                );
                return $e->creerCompte($email, $motDePasse);

            case 'PROFESSEUR':
                $p = new Professeur(
                    $data['idUtilisateur'], $data['nom'], $data['prenom'], $data['email'],
                    $data['specialite'] ?? '',
                    $data['grade']      ?? '',
                    $data['filiere']    ?? ''
                );
                return $p->creerCompte($email, $motDePasse);

            case 'DE':
            case 'BIBLIOTHECAIRE':
                return (bool) CompteRepository::create($data['idUtilisateur'], $email, $motDePasse);
        }

        return false;
    }

    /** Lit les informations spécifiques au rôle de l'utilisateur. */
    private static function chargerDetailsRole(string $idUtilisateur): array
    {
        $pdo    = getConnexion();
        $tables = [
            'ETUDIANT'       => 'etudiant',
            'PROFESSEUR'     => 'professeur',
            'DE'             => 'de',
            'BIBLIOTHECAIRE' => 'bibliothecaire',
        ];
        foreach ($tables as $role => $table) {
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE idUtilisateur = ?");
            $stmt->execute([$idUtilisateur]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return ['role' => $role, 'data' => $row];
        }
        return ['role' => 'UNKNOWN', 'data' => []];
    }

    /** Redirige vers la page d'inscription avec un message d'erreur. */
    private static function backRegister(string $message): void
    {
        $_SESSION['erreur'] = $message;
        header('Location: ../View/auth/register.php');
        exit;
    }

    /* ================================================================
     *  CONNEXION / DÉCONNEXION
     * ================================================================ */

    public static function login(): void
    {
        if (!isset($_POST['btn_login'])) return;

        $email      = trim($_POST['email']      ?? '');
        $motDePasse = trim($_POST['motDePasse'] ?? '');

        $_SESSION['form_login'] = ['email' => $email];

        if ($email === '' || $motDePasse === '') {
            $_SESSION['erreur'] = "Veuillez saisir votre e-mail et votre mot de passe.";
            header('Location: ../View/auth/login.php'); exit;
        }

        $compte = CompteRepository::login($email, $motDePasse);
        if (!$compte) {
            $_SESSION['erreur'] = "E-mail ou mot de passe incorrect.";
            header('Location: ../View/auth/login.php'); exit;
        }

        unset($_SESSION['form_login']);

        $_SESSION['idCompte']      = $compte['idCompte'];
        $_SESSION['idUtilisateur'] = $compte['idUtilisateur'];
        $_SESSION['email']         = $compte['email'];
        $_SESSION['username']      = $compte['username'];   // = email (rétro-compat)
        $_SESSION['nom']           = $compte['nom'];
        $_SESSION['prenom']        = $compte['prenom'];
        $_SESSION['role']          = $compte['role'];
        $_SESSION['peutUploader']  = $compte['peutUploader'] ?? 0;

        $dashboards = [
            'DE'             => '../View/de/dashboard.php',
            'PROFESSEUR'     => '../View/professeur/dashboard.php',
            'BIBLIOTHECAIRE' => '../View/bibliothecaire/dashboard.php',
            'ETUDIANT'       => '../View/etudiant/dashboard.php',
        ];
        header('Location: ' . ($dashboards[$compte['role']] ?? '../View/etudiant/dashboard.php'));
        exit;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: ../View/auth/login.php');
        exit;
    }

    /* ================================================================
     *  GARDES D'ACCÈS
     * ================================================================ */

    public static function requireLogin(): void
    {
        if (!isset($_SESSION['idCompte'])) {
            header('Location: ' . self::loginPath());
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (($_SESSION['role'] ?? '') !== $role) {
            header('Location: ' . self::loginPath());
            exit;
        }
    }

    /** Calcule le chemin relatif vers la page de connexion à partir du script appelant. */
    private static function loginPath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($script, '/View/') !== false) {
            return '../../View/auth/login.php';
        }
        return 'View/auth/login.php';
    }

    /* ================================================================
     *  CHANGEMENT DE MOT DE PASSE (formulaire interne au profil)
     * ================================================================ */

    public static function changerMotDePasse(): void
    {
        self::requireLogin();
        if (!isset($_POST['btn_changer_mdp'])) return;

        $actuel    = trim($_POST['mot_de_passe_actuel']    ?? '');
        $nouveau   = trim($_POST['nouveau_mot_de_passe']   ?? '');
        $confirmer = trim($_POST['confirmer_mot_de_passe'] ?? '');

        $pdo  = getConnexion();
        $stmt = $pdo->prepare("SELECT motDePasse FROM compte WHERE idCompte = ?");
        $stmt->execute([$_SESSION['idCompte']]);
        $compte = $stmt->fetch();

        $retour = '../View/' . strtolower($_SESSION['role']) . '/profil.php';

        if (!$compte || !password_verify($actuel, $compte['motDePasse'])) {
            $_SESSION['erreur'] = "Mot de passe actuel incorrect.";
            header('Location: ' . $retour); exit;
        }
        if ($nouveau !== $confirmer) {
            $_SESSION['erreur'] = "Les deux nouveaux mots de passe ne correspondent pas.";
            header('Location: ' . $retour); exit;
        }
        if (strlen($nouveau) < 6) {
            $_SESSION['erreur'] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            header('Location: ' . $retour); exit;
        }

        CompteRepository::changerMotDePasse($_SESSION['idCompte'], $nouveau);
        $_SESSION['succes'] = "Mot de passe mis à jour.";
        header('Location: ' . $retour); exit;
    }
}
