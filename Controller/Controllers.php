<?php
ob_start();

require_once __DIR__ . '/../classes/Memoire.php';
require_once __DIR__ . '/../classes/Commentaire.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../Controller/AuthController.php';
require_once __DIR__ . '/../Repository/MemoireRepository.php';
require_once __DIR__ . '/../Repository/CompteRepository.php';
require_once __DIR__ . '/../Repository/NotificationRepository.php';

class MemoireController {

    public static function index(): array {
        return MemoireRepository::findAll(
            $_GET['filiere'] ?? '',
            $_GET['annee'] ?? '',
            $_GET['centre'] ?? '',
            $_GET['q'] ?? '',
            $_GET['type'] ?? ''
        );
    }

    public static function show(string $id): ?array {
        return MemoireRepository::findById($id);
    }

    // UPLOAD PDF CORRIGÉ
    public static function uploadPdf() {

        if (!isset($_FILES['fichierPdf']) || $_FILES['fichierPdf']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $dir = __DIR__ . '/../uploads/memoires/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = 'memoire_' . time() . '_' . bin2hex(random_bytes(3)) . '.pdf';
        $path = $dir . $name;

        if (!move_uploaded_file($_FILES['fichierPdf']['tmp_name'], $path)) {
            return null;
        }

        return $name;
    }

    // MÉTHODE SOUMETTRE CORRIGÉE
    public static function soumettre() {

        AuthController::requireLogin();
        $pdo = getConnexion();

        $modeCorrection = isset($_POST['mode_correction']) ? (bool)$_POST['mode_correction'] : false;
        $modeValidBinome = isset($_POST['mode_valider_binome']) ? (bool)$_POST['mode_valider_binome'] : false;

        // ── CAS 1 : VALIDATION DU BINÔME ──────────────────────────────────────
        if ($modeValidBinome) {
            $idMemoire = trim($_POST['idMemoire'] ?? '');
            if (!$idMemoire) {
                $_SESSION['erreur'] = "ID mémoire manquant";
                header('Location: ../View/etudiant/upload.php');
                exit;
            }

            // Vérifier que le co-auteur est bien autorisé
            $stmtAuth = $pdo->prepare("
                SELECT ca.* FROM co_auteur ca
                WHERE ca.idMemoire = ? AND ca.idCompte = ? AND ca.statut = 'ACCEPTE'
            ");
            $stmtAuth->execute([$idMemoire, $_SESSION['idCompte']]);
            if (!$stmtAuth->fetch()) {
                $_SESSION['erreur'] = "Vous n'êtes pas autorisé à valider ce mémoire";
                header('Location: ../View/etudiant/upload.php');
                exit;
            }

            // Mettre à jour le statut du mémoire de BROUILLON à EN_ATTENTE
            MemoireRepository::updateStatut($idMemoire, 'EN_ATTENTE');

            // Récupérer les infos du mémoire pour notifier
            $stmtMem = $pdo->prepare("
                SELECT m.theme, m.idCompte, m.idProf, u.prenom, u.nom
                FROM memoire m
                JOIN compte c ON m.idCompte = c.idCompte
                JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                WHERE m.idMemoire = ?
            ");
            $stmtMem->execute([$idMemoire]);
            $memData = $stmtMem->fetch(PDO::FETCH_ASSOC);

            if ($memData) {
                $nomAuteur = $memData['prenom'] . ' ' . $memData['nom'];
                $nomCoAuteur = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];

                // Notifier l'auteur principal
                $msgAuteur = "Votre co-auteur $nomCoAuteur a validé le mémoire. Transmission à l'encadreur.";
                NotificationRepository::create(
                    $memData['idCompte'],
                    $msgAuteur,
                    'MEMOIRE_VALIDE_BINOME',
                    $idMemoire
                );

                // Notifier le professeur encadreur
                $stmtProf = $pdo->prepare("
                    SELECT c.idCompte FROM compte c
                    JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                    WHERE u.idUtilisateur = ?
                ");
                $stmtProf->execute([$memData['idProf']]);
                $profData = $stmtProf->fetch(PDO::FETCH_ASSOC);

                if ($profData) {
                    $msgProf = "Nouveau mémoire (binôme) à valider : \"" . $memData['theme'] . "\" de $nomAuteur et $nomCoAuteur";
                    NotificationRepository::create(
                        $profData['idCompte'],
                        $msgProf,
                        'MEMOIRE_A_VALIDER',
                        $idMemoire
                    );
                }
            }

            $_SESSION['succes'] = "Mémoire validé et transmis à l'encadreur";
            header('Location: ../View/etudiant/dashboard.php');
            exit;
        }

        // ── CAS 2 : SOUMISSION DE CORRECTION APRÈS REJET ───────────────────────
        if ($modeCorrection) {
            $idMemoire = trim($_POST['idMemoire'] ?? '');
            if (!$idMemoire) {
                $_SESSION['erreur'] = "ID mémoire manquant";
                header('Location: ../View/etudiant/upload.php');
                exit;
            }

            // Vérifier que le mémoire appartient bien à l'étudiant et est REJETE
            $stmtCheck = $pdo->prepare("
                SELECT * FROM memoire WHERE idMemoire = ? AND idCompte = ? AND statut = 'REJETE'
            ");
            $stmtCheck->execute([$idMemoire, $_SESSION['idCompte']]);
            if (!$stmtCheck->fetch()) {
                $_SESSION['erreur'] = "Ce mémoire n'existe pas ou n'est pas en rejet";
                header('Location: ../View/etudiant/upload.php');
                exit;
            }

            // Uploader le nouveau PDF
            $pdf = null;
            if (isset($_FILES['fichierPdf']) && $_FILES['fichierPdf']['error'] === UPLOAD_ERR_OK) {
                $pdf = self::uploadPdf();
            }

            // Mettre à jour le mémoire avec le nouveau PDF et statut EN_ATTENTE
            $stmtUpdate = $pdo->prepare("
                UPDATE memoire SET fichierPdf = ?, statut = 'EN_ATTENTE' WHERE idMemoire = ?
            ");
            $stmtUpdate->execute([$pdf, $idMemoire]);

            // Récupérer les infos du mémoire
            $stmtMem = $pdo->prepare("
                SELECT m.theme, m.idProf FROM memoire WHERE idMemoire = ?
            ");
            $stmtMem->execute([$idMemoire]);
            $memData = $stmtMem->fetch(PDO::FETCH_ASSOC);

            if ($memData) {
                // Notifier le professeur de la correction
                $stmtProf = $pdo->prepare("
                    SELECT c.idCompte FROM compte c
                    JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                    WHERE u.idUtilisateur = ?
                ");
                $stmtProf->execute([$memData['idProf']]);
                $profData = $stmtProf->fetch(PDO::FETCH_ASSOC);

                if ($profData) {
                    $nomEtudiant = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
                    $msgProf = "Correction reçue - Mémoire à revalider : \"" . $memData['theme'] . "\" de $nomEtudiant";
                    NotificationRepository::create(
                        $profData['idCompte'],
                        $msgProf,
                        'MEMOIRE_A_VALIDER',
                        $idMemoire
                    );
                }
            }

            $_SESSION['succes'] = "Correction soumise avec succès";
            header('Location: ../View/etudiant/dashboard.php');
            exit;
        }

        // ── CAS 3 : NOUVELLE SOUMISSION ──────────────────────────────────────
        $theme = trim($_POST['theme'] ?? '');
        $idProf = trim($_POST['idProf'] ?? '');
        $annee = trim($_POST['anneeAcademique'] ?? '');
        $centre = trim($_POST['centre'] ?? '');
        $coAuteurMatricule = trim($_POST['co_auteur_matricule'] ?? '');

        if ($theme === '' || $idProf === '') {
            $_SESSION['erreur'] = "Données invalides";
            header('Location: ../View/etudiant/dashboard.php');
            exit;
        }

        $pdf = null;
        if (isset($_FILES['fichierPdf']) && $_FILES['fichierPdf']['error'] === UPLOAD_ERR_OK) {
            $pdf = self::uploadPdf();
        }

        try {
            // Déterminer le statut selon si binôme ou solo
            $statut = empty($coAuteurMatricule) ? 'EN_ATTENTE' : 'BROUILLON';

            // Inclure l'id du compte de l'utilisateur connecté
            $stmt = $pdo->prepare("
                INSERT INTO memoire (idCompte, theme, idProf, anneeAcademique, centre, fichierPdf, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([$_SESSION['idCompte'], $theme, $idProf, $annee, $centre, $pdf, $statut]);
            
            // Récupérer l'ID auto-généré
            $lastId = $pdo->lastInsertId();

            // Récupérer le compte du professeur via son idUtilisateur
            $stmtProf = $pdo->prepare("
                SELECT c.idCompte, u.nom, u.prenom 
                FROM compte c 
                JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                WHERE u.idUtilisateur = ?
            ");
            $stmtProf->execute([$idProf]);
            $profData = $stmtProf->fetch(PDO::FETCH_ASSOC);

            if ($profData) {
                $nomEtudiant = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
                if (empty($coAuteurMatricule)) {
                    $message = "Nouveau mémoire à valider de $nomEtudiant : \"$theme\"";
                } else {
                    $message = "Nouveau mémoire en binôme soumis par $nomEtudiant : \"$theme\". Le co-auteur doit encore valider son invitation.";
                }
                NotificationRepository::create(
                    $profData['idCompte'],
                    $message,
                    'MEMOIRE_A_VALIDER',
                    $lastId
                );
            }

            if (!empty($coAuteurMatricule)) {
                // Si binôme → chercher le co-auteur et créer une invitation
                $stmtCoAuteur = $pdo->prepare("
                    SELECT c.idCompte FROM compte c
                    JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                    WHERE u.idUtilisateur = ?
                ");
                $stmtCoAuteur->execute([$coAuteurMatricule]);
                $coAuteurData = $stmtCoAuteur->fetch(PDO::FETCH_ASSOC);

                if ($coAuteurData) {
                    // Ajouter le co-auteur avec statut EN_ATTENTE
                    MemoireRepository::ajouterCoAuteur($lastId, $coAuteurData['idCompte']);

                    // Créer une notification d'invitation au co-auteur
                    $nomAuteur = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
                    $msgCoAuteur = "$nomAuteur vous invite à valider le mémoire : \"$theme\"";
                    NotificationRepository::create(
                        $coAuteurData['idCompte'],
                        $msgCoAuteur,
                        'INVITATION_MEMOIRE',
                        $lastId
                    );
                }
            }
            
            $_SESSION['succes'] = "Mémoire soumis avec succès (ID: " . $lastId . ")";
            
        } catch (PDOException $e) {
            // Gestion des erreurs
            $_SESSION['erreur'] = "Erreur lors de la soumission: " . $e->getMessage();
        }
        
        header('Location: ../View/etudiant/dashboard.php');
        exit;
    }

    // Répondre à une invitation de co-auteur
    public static function repondreInvitation(): void {
        AuthController::requireLogin();
        $pdo = getConnexion();

        $idMemoire = trim($_POST['idMemoire'] ?? '');
        $reponse = trim($_POST['reponse'] ?? '');

        if (!$idMemoire || !in_array($reponse, ['ACCEPTE', 'REFUSE'])) {
            $_SESSION['erreur'] = "Données invalides";
            header('Location: ../View/etudiant/dashboard.php');
            exit;
        }

        // Mettre à jour le statut du co-auteur
        MemoireRepository::repondreInvitation($idMemoire, $_SESSION['idCompte'], $reponse);

        // Récupérer les infos du mémoire
        $stmtMem = $pdo->prepare("
            SELECT m.theme, m.idCompte, u.prenom, u.nom
            FROM memoire m
            JOIN compte c ON m.idCompte = c.idCompte
            JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
            WHERE m.idMemoire = ?
        ");
        $stmtMem->execute([$idMemoire]);
        $memData = $stmtMem->fetch(PDO::FETCH_ASSOC);

        if ($memData) {
            $nomCoAuteur = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
            $nomAuteur = $memData['prenom'] . ' ' . $memData['nom'];

            if ($reponse === 'ACCEPTE') {
                // Notifier l'auteur principal que le co-auteur a accepté
                $msgAuteur = "$nomCoAuteur a accepté l'invitation. Vous pouvez tous deux valider le mémoire.";
                NotificationRepository::create(
                    $memData['idCompte'],
                    $msgAuteur,
                    'INVITATION_ACCEPTEE',
                    $idMemoire
                );
            } else {
                // Notifier l'auteur principal que le co-auteur a refusé
                $msgAuteur = "$nomCoAuteur a refusé l'invitation au mémoire.";
                NotificationRepository::create(
                    $memData['idCompte'],
                    $msgAuteur,
                    'INVITATION_REFUSEE',
                    $idMemoire
                );
            }
        }

        $_SESSION['succes'] = $reponse === 'ACCEPTE' ? "Invitation acceptée" : "Invitation refusée";
        header('Location: ../View/etudiant/dashboard.php');
        exit;
    }

    // Gérer une invitation
    public static function gererInvitation(): void {
        // Placeholder - peut être étendu selon les besoins
        header('Location: ../View/etudiant/dashboard.php');
        exit;
    }

    // Ajouter un commentaire sur un mémoire
    public static function commenter(): void {
        AuthController::requireLogin();
        if (!isset($_POST['btn_commenter'])) return;

        $idMemoire = trim($_POST['idMemoire'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        $idParent = trim($_POST['parentId'] ?? '') ?: null;

        if (!$idMemoire || !$contenu) {
            $_SESSION['erreur'] = "Données invalides";
            header('Location: ../View/shared/detail_memoire.php?id=' . urlencode($idMemoire));
            exit;
        }

        // Créer le commentaire
        $pdo = getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM commentaire");
        $count = $stmt->fetchColumn();
        $idCom = 'COM' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $stmtInsert = $pdo->prepare("
            INSERT INTO commentaire (idCommentaire, idCompte, idMemoire, idParent, contenu, note)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $stmtInsert->execute([$idCom, $_SESSION['idCompte'], $idMemoire, $idParent, $contenu]);

        $_SESSION['succes'] = "Commentaire ajouté";
        header('Location: ../View/shared/detail_memoire.php?id=' . urlencode($idMemoire));
        exit;
    }

    // Liker un mémoire
    public static function liker(): void {
        AuthController::requireLogin();
        if (!isset($_POST['action_like'])) return;

        $idMemoire = trim($_POST['idMemoire'] ?? '');
        $action = trim($_POST['action_like'] ?? '');

        if (!$idMemoire || !in_array($action, ['like', 'unlike'])) {
            $_SESSION['erreur'] = "Données invalides";
            header('Location: ../View/shared/detail_memoire.php?id=' . urlencode($idMemoire));
            exit;
        }

        $pdo = getConnexion();

        if ($action === 'like') {
            // Vérifier que le like n'existe pas déjà
            $stmtCheck = $pdo->prepare("SELECT 1 FROM likes WHERE idCompte = ? AND idMemoire = ?");
            $stmtCheck->execute([$_SESSION['idCompte'], $idMemoire]);
            if (!$stmtCheck->fetch()) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM likes");
                $count = $stmt->fetchColumn();
                $idLike = 'LIKE' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

                $stmtInsert = $pdo->prepare("
                    INSERT INTO likes (idLike, idCompte, idMemoire) VALUES (?, ?, ?)
                ");
                $stmtInsert->execute([$idLike, $_SESSION['idCompte'], $idMemoire]);

                // Incrémenter le compteur de likes
                $pdo->prepare("UPDATE memoire SET nbLikes = nbLikes + 1 WHERE idMemoire = ?")
                    ->execute([$idMemoire]);
            }
        } else {
            // Unlike
            $stmtDelete = $pdo->prepare("DELETE FROM likes WHERE idCompte = ? AND idMemoire = ?");
            $stmtDelete->execute([$_SESSION['idCompte'], $idMemoire]);

            // Décrémenter le compteur de likes
            $pdo->prepare("UPDATE memoire SET nbLikes = GREATEST(0, nbLikes - 1) WHERE idMemoire = ?")
                ->execute([$idMemoire]);
        }

        $_SESSION['succes'] = $action === 'like' ? "Mémoire aimé" : "Mémoire retiré des favoris";
        header('Location: ../View/shared/detail_memoire.php?id=' . urlencode($idMemoire));
        exit;
    }
}

class ValidationController {

    public static function valider(): void {
        AuthController::requireRole('PROFESSEUR');

        if (!isset($_POST['btn_valider'])) return;

        $pdo = getConnexion();
        $idMemoire = trim($_POST['idMemoire']);
        MemoireRepository::updateStatut($idMemoire, 'EN_ATTENTE_DIRECTION');

        // Récupérer les infos du mémoire
        $stmtMem = $pdo->prepare("
            SELECT m.theme, m.idCompte, u.prenom, u.nom
            FROM memoire m
            JOIN compte c ON m.idCompte = c.idCompte
            JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
            WHERE m.idMemoire = ?
        ");
        $stmtMem->execute([$idMemoire]);
        $memData = $stmtMem->fetch(PDO::FETCH_ASSOC);

        if ($memData) {
            // Notifier l'étudiant que son mémoire a été validé par le prof
            $nomEtudiant = $memData['prenom'] . ' ' . $memData['nom'];
            $msgEtu = "Votre mémoire \"" . $memData['theme'] . "\" a été validé par votre encadreur. En attente de la Direction des Études.";
            NotificationRepository::create(
                $memData['idCompte'],
                $msgEtu,
                'MEMOIRE_VALIDE_PROF',
                $idMemoire
            );

            // Récupérer le compte de la Direction des Études (rôle 'DE')
            $stmtDE = $pdo->prepare("
                SELECT c.idCompte, u.prenom, u.nom
                FROM compte c
                JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                WHERE u.role = 'DE'
                LIMIT 1
            ");
            $stmtDE->execute();
            $deData = $stmtDE->fetch(PDO::FETCH_ASSOC);

            if ($deData) {
                // Notifier la DE
                $msgDE = "Mémoire validé par le prof - À valider : \"" . $memData['theme'] . "\" de $nomEtudiant";
                NotificationRepository::create(
                    $deData['idCompte'],
                    $msgDE,
                    'MEMOIRE_A_VALIDER_DE',
                    $idMemoire
                );
            }
        }

        $_SESSION['succes'] = "Mémoire validé";
        header('Location: ../View/professeur/validation.php');
        exit;
    }

    public static function rejeter(): void {
        AuthController::requireRole('PROFESSEUR');

        if (!isset($_POST['btn_rejeter'])) return;

        $pdo = getConnexion();
        $idMemoire = trim($_POST['idMemoire']);
        $commentaire = trim($_POST['commentaireJury']);

        MemoireRepository::updateStatut($idMemoire, 'REJETE', $commentaire);

        // Récupérer les infos du mémoire et de l'étudiant
        $stmtMem = $pdo->prepare("
            SELECT m.theme, m.idCompte
            FROM memoire m
            WHERE m.idMemoire = ?
        ");
        $stmtMem->execute([$idMemoire]);
        $memData = $stmtMem->fetch(PDO::FETCH_ASSOC);

        if ($memData) {
            // Notifier l'étudiant du rejet
            $msgRejet = "Votre mémoire \"" . $memData['theme'] . "\" a été REJETÉ.\n\nMotif : " . $commentaire . "\n\nVeuillez soumettre une version corrigée.";
            NotificationRepository::create(
                $memData['idCompte'],
                $msgRejet,
                'MEMOIRE_REJETE',
                $idMemoire
            );
        }

        $_SESSION['succes'] = "Mémoire rejeté";
        header('Location: ../View/professeur/validation.php');
        exit;
    }

    public static function publier(): void {
        AuthController::requireLogin();

        if (!isset($_POST['btn_publier'])) return;

        $pdo = getConnexion();
        $idMemoire = trim($_POST['idMemoire']);
        MemoireRepository::updateStatut($idMemoire, 'VALIDE');

        // Récupérer les infos du mémoire et de l'étudiant
        $stmtMem = $pdo->prepare("
            SELECT m.theme, m.idCompte
            FROM memoire m
            WHERE m.idMemoire = ?
        ");
        $stmtMem->execute([$idMemoire]);
        $memData = $stmtMem->fetch(PDO::FETCH_ASSOC);

        if ($memData) {
            // Notifier l'étudiant que son mémoire est publié
            $msgPub = "Félicitations ! Votre mémoire \"" . $memData['theme'] . "\" a été validé et publié dans la bibliothèque.";
            NotificationRepository::create(
                $memData['idCompte'],
                $msgPub,
                'MEMOIRE_PUBLIE',
                $idMemoire
            );
        }

        $_SESSION['succes'] = "Mémoire publié";
        header('Location: ../index.php');
        exit;
    }

    public static function gererDelegation(): void {
        AuthController::requireRole('DE');
        
        if (isset($_POST['btn_activer_delegation'])) {
            $idBib = trim($_POST['idBibliothecaire'] ?? '');
            if ($idBib) {
                MemoireRepository::activerDelegation($_SESSION['idUtilisateur'], $idBib);
                $_SESSION['succes'] = "Délégation activée";
            }
        } elseif (isset($_POST['btn_desactiver_delegation'])) {
            MemoireRepository::desactiverDelegation();
            $_SESSION['succes'] = "Délégation désactivée";
        }

        header('Location: ../View/de/dashboard.php');
        exit;
    }
}

class AdminController {

    public static function enregistrerEtudiant(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_enregistrer_etu'])) return;

        $pdo = getConnexion();
        $id = trim($_POST['idUtilisateur']);

        $stmt = $pdo->prepare("SELECT idUtilisateur FROM utilisateur WHERE idUtilisateur=?");
        $stmt->execute([$id]);

        if ($stmt->fetch()) {
            $pdo->prepare("UPDATE utilisateur SET nom=?,prenom=?,email=? WHERE idUtilisateur=?")
                ->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $id]);
        } else {
            $pdo->prepare("INSERT INTO utilisateur (idUtilisateur,nom,prenom,email,role)
                VALUES (?,?,?,?, 'ETUDIANT')")
                ->execute([$id, $_POST['nom'], $_POST['prenom'], $_POST['email']]);
        }

        $_SESSION['succes'] = "Étudiant enregistré";
        header('Location: ../View/de/liste_etudiants.php');
        exit;
    }

    public static function supprimerEtudiant(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_supprimer_etu'])) return;

        getConnexion()->prepare("DELETE FROM utilisateur WHERE idUtilisateur=?")
            ->execute([$_POST['idUtilisateur']]);

        $_SESSION['succes'] = "Étudiant supprimé";
        header('Location: ../View/de/liste_etudiants.php');
        exit;
    }

    public static function modifierEtudiant(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_modifier_etu'])) return;

        $pdo = getConnexion();
        $id = trim($_POST['idUtilisateur']);

        $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, email=? WHERE idUtilisateur=?")
            ->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $id]);

        $_SESSION['succes'] = "Étudiant modifié";
        header('Location: ../View/de/liste_etudiants.php');
        exit;
    }

    public static function enregistrerProf(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_enregistrer_prof'])) return;

        $pdo = getConnexion();
        $id = trim($_POST['idUtilisateur']);

        $stmt = $pdo->prepare("SELECT idUtilisateur FROM utilisateur WHERE idUtilisateur=?");
        $stmt->execute([$id]);

        if ($stmt->fetch()) {
            $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, email=? WHERE idUtilisateur=?")
                ->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $id]);
        } else {
            $pdo->prepare("INSERT INTO utilisateur (idUtilisateur, nom, prenom, email, role) VALUES (?, ?, ?, ?, 'PROFESSEUR')")
                ->execute([$id, $_POST['nom'], $_POST['prenom'], $_POST['email']]);
        }

        $_SESSION['succes'] = "Professeur enregistré";
        header('Location: ../View/de/liste_profs.php');
        exit;
    }

    public static function modifierProf(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_modifier_prof'])) return;

        $pdo = getConnexion();
        $id = trim($_POST['idUtilisateur']);

        $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, email=? WHERE idUtilisateur=?")
            ->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $id]);

        $_SESSION['succes'] = "Professeur modifié";
        header('Location: ../View/de/liste_profs.php');
        exit;
    }

    public static function supprimerProf(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_supprimer_prof'])) return;

        getConnexion()->prepare("DELETE FROM utilisateur WHERE idUtilisateur=?")
            ->execute([$_POST['idUtilisateur']]);

        $_SESSION['succes'] = "Professeur supprimé";
        header('Location: ../View/de/liste_profs.php');
        exit;
    }

    public static function activerUpload(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_activer_upload'])) return;

        $pdo = getConnexion();
        $idUtilisateur = trim($_POST['idUtilisateur']);

        $pdo->prepare("UPDATE etudiant SET peutUploader = 1 WHERE idUtilisateur = ?")
            ->execute([$idUtilisateur]);

        $_SESSION['succes'] = "Étudiant autorisé à uploader";
        header('Location: ../View/de/liste_etudiants.php');
        exit;
    }

    public static function desactiverUpload(): void {
        AuthController::requireRole('DE');

        if (!isset($_POST['btn_desactiver_upload'])) return;

        $pdo = getConnexion();
        $idUtilisateur = trim($_POST['idUtilisateur']);

        $pdo->prepare("UPDATE etudiant SET peutUploader = 0 WHERE idUtilisateur = ?")
            ->execute([$idUtilisateur]);

        $_SESSION['succes'] = "Étudiant bloqué pour upload";
        header('Location: ../View/de/liste_etudiants.php');
        exit;
    }
}
?>