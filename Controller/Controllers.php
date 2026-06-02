<?php
ob_start();
require_once __DIR__ . '/../classes/Memoire.php';
require_once __DIR__ . '/../classes/Commentaire.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../Controller/AuthController.php';
require_once __DIR__ . '/../Repository/MemoireRepository.php';
require_once __DIR__ . '/../Repository/CompteRepository.php';
require_once __DIR__ . '/../Repository/NotificationRepository.php';

// ───────────────────────────────────────────
// MEMOIRE CONTROLLER
// ───────────────────────────────────────────
class MemoireController {

    public static function index(): array {
        return MemoireRepository::findAll($_GET['filiere']??'',$_GET['annee']??'',$_GET['centre']??'',$_GET['q']??'',$_GET['type']??'');
    }

    public static function show(string $id): ?array { return MemoireRepository::findById($id); }

    private static function uploadPdf() {
        if (!isset($_FILES['fichierPdf']) || $_FILES['fichierPdf']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erreur'] = "Veuillez sélectionner un fichier PDF valide.";
            return false;
        }
        $fichier = $_FILES['fichierPdf'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $_SESSION['erreur'] = "Seuls les fichiers PDF sont autorisés.";
            return false;
        }
        if ($fichier['size'] > 10 * 1024 * 1024) {
            $_SESSION['erreur'] = "Le fichier ne doit pas dépasser 10 Mo.";
            return false;
        }
        $uploadDir = __DIR__ . '/../uploads/memoires/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $nomFichier = uniqid('memoire_') . '.pdf';
        $destination = $uploadDir . $nomFichier;
        if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
            $_SESSION['erreur'] = "Erreur lors de l'enregistrement du fichier.";
            return false;
        }
        return $nomFichier;
    }

    public static function upload(): void {
        AuthController::requireLogin();
        if (!isset($_POST['btn_soumettre'])) return;

        $pdo = getConnexion();

        // Vérifier que l'upload est autorisé (depuis la base, pas juste la session)
        $stmt = $pdo->prepare("SELECT peutUploader FROM etudiant WHERE idUtilisateur = ?");
        $stmt->execute([$_SESSION['idUtilisateur']]);
        if (!(bool)$stmt->fetchColumn()) {
            $_SESSION['erreur'] = "Vous n'êtes pas autorisé à soumettre un mémoire.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        // ── CAS : CORRECTION PDF après rejet ──────────────────────────────
        // Si le mémoire existe déjà en REJETE ou BROUILLON (correction binôme)
        // on met juste à jour le PDF, on ne recrée pas tout
        if (isset($_POST['mode_correction']) && $_POST['mode_correction'] === '1') {
            $idMemoire = trim($_POST['idMemoire']);
            $stmt = $pdo->prepare("SELECT * FROM memoire WHERE idMemoire = ?");
            $stmt->execute([$idMemoire]);
            $memoireExistant = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$memoireExistant) {
                $_SESSION['erreur'] = "Mémoire introuvable.";
                header('Location: ../View/etudiant/dashboard.php'); exit;
            }

            // Upload nouveau PDF
            $pdf = self::uploadPdf();
            if ($pdf === false) { header('Location: ../View/etudiant/upload.php?correction=1&id='.$idMemoire); exit; }

            // Supprimer ancien PDF
            if ($memoireExistant['fichierPdf']) {
                $old = __DIR__.'/../uploads/memoires/'.$memoireExistant['fichierPdf'];
                if (file_exists($old)) unlink($old);
            }

            // Vérifier si binôme
            $stmt = $pdo->prepare("SELECT * FROM co_auteur WHERE idMemoire = ? AND statut = 'ACCEPTE'");
            $stmt->execute([$idMemoire]);
            $coAuteur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($coAuteur) {
                // BINÔME : mettre en BROUILLON_CORRECTION, notifier l'autre
                $pdo->prepare("UPDATE memoire SET fichierPdf = ?, statut = 'BROUILLON', commentaireJury = NULL WHERE idMemoire = ?")
                    ->execute([$pdf, $idMemoire]);

                // Qui est l'autre ? Auteur principal ou co-auteur ?
                $stmt = $pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire = ?");
                $stmt->execute([$idMemoire]);
                $idComptePrincipal = $stmt->fetchColumn();

                $autreCompte = ($idComptePrincipal === $_SESSION['idCompte'])
                    ? $coAuteur['idCompte']
                    : $idComptePrincipal;

                NotificationRepository::create($autreCompte,
                    "🔄 " . $_SESSION['prenom'] . " " . $_SESSION['nom'] . " a soumis un nouveau PDF pour le mémoire \"" . $memoireExistant['theme'] . "\". Veuillez valider pour l'envoyer à l'encadreur.",
                    'INVITATION', $idMemoire);

                $_SESSION['succes'] = "Nouveau PDF soumis. Votre co-auteur doit valider avant que le mémoire soit transmis à l'encadreur.";
            } else {
                // SOLO : remettre directement en EN_ATTENTE
                $pdo->prepare("UPDATE memoire SET fichierPdf = ?, statut = 'EN_ATTENTE', commentaireJury = NULL WHERE idMemoire = ?")
                    ->execute([$pdf, $idMemoire]);

                // Notifier le prof
                $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur = ?");
                $stmt->execute([$memoireExistant['idProf']]);
                $cProf = $stmt->fetchColumn();
                if ($cProf) NotificationRepository::create($cProf,
                    "🔄 Version corrigée : \"" . $memoireExistant['theme'] . "\" — " . $_SESSION['prenom'] . " " . $_SESSION['nom'],
                    'SOUMISSION', $idMemoire);

                $_SESSION['succes'] = "Version corrigée soumise. En attente de validation par votre encadreur.";
            }

            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        // ── CAS : VALIDATION BINÔME après que l'autre a uploadé le PDF ────
        if (isset($_POST['mode_valider_binome']) && $_POST['mode_valider_binome'] === '1') {
            $idMemoire = trim($_POST['idMemoire']);
            // Passer en EN_ATTENTE et notifier le prof
            $stmt = $pdo->prepare("SELECT * FROM memoire WHERE idMemoire = ?");
            $stmt->execute([$idMemoire]);
            $mem = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdo->prepare("UPDATE memoire SET statut = 'EN_ATTENTE' WHERE idMemoire = ?")
                ->execute([$idMemoire]);

            $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur = ?");
            $stmt->execute([$mem['idProf']]);
            $cProf = $stmt->fetchColumn();
            if ($cProf) NotificationRepository::create($cProf,
                "🔄 Version corrigée (binôme validée) : \"" . $mem['theme'] . "\"",
                'SOUMISSION', $idMemoire);

            $_SESSION['succes'] = "Mémoire validé et transmis à l'encadreur.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        // ── CAS : NOUVELLE SOUMISSION ──────────────────────────────────────
        // Bloquer si mémoire déjà en cours
        $stmt = $pdo->prepare("SELECT idMemoire FROM memoire WHERE idCompte=? AND statut IN('BROUILLON','EN_ATTENTE','EN_ATTENTE_DIRECTION')");
        $stmt->execute([$_SESSION['idCompte']]);
        if ($stmt->fetch()) {
            $_SESSION['erreur'] = "Vous avez déjà un mémoire en cours.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        // Vérifier aussi co-auteur avec mémoire en cours
        $stmt = $pdo->prepare("SELECT m.idMemoire FROM co_auteur ca JOIN memoire m ON ca.idMemoire=m.idMemoire WHERE ca.idCompte=? AND ca.statut='ACCEPTE' AND m.statut IN('BROUILLON','EN_ATTENTE','EN_ATTENTE_DIRECTION')");
        $stmt->execute([$_SESSION['idCompte']]);
        if ($stmt->fetch()) {
            $_SESSION['erreur'] = "Vous êtes co-auteur d'un mémoire déjà en cours.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        $pdf = self::uploadPdf();
        if ($pdf === false) { header('Location: ../View/etudiant/upload.php'); exit; }

        $stmt = $pdo->prepare("SELECT filiere, option_etu FROM etudiant WHERE idUtilisateur = ?");
        $stmt->execute([$_SESSION['idUtilisateur']]);
        $inf = $stmt->fetch(PDO::FETCH_ASSOC);

        $memoire = new Memoire('', $_SESSION['idCompte'], trim($_POST['idProf']),
            trim($_POST['theme']), trim($_POST['anneeAcademique']), trim($_POST['centre']),
            'NOUVEAU', 'BROUILLON', $pdf, true, $inf['filiere']??'', $inf['option_etu']??'');

        if (!$memoire->save()) {
            $_SESSION['erreur'] = "Erreur lors de la création du mémoire.";
            header('Location: ../View/etudiant/upload.php'); exit;
        }

        $coMatricule = trim($_POST['co_auteur_matricule'] ?? '');
        if ($coMatricule !== '') {
            // Binôme
            $stmt = $pdo->prepare("SELECT c.idCompte, u.nom, u.prenom FROM compte c JOIN utilisateur u ON c.idUtilisateur=u.idUtilisateur WHERE c.idUtilisateur=?");
            $stmt->execute([$coMatricule]);
            $coData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($coData) {
                MemoireRepository::ajouterCoAuteur($memoire->getIdMemoire(), $coData['idCompte']);
                NotificationRepository::create($coData['idCompte'],
                    "🤝 " . $_SESSION['prenom'] . " " . $_SESSION['nom'] . " vous invite comme co-auteur pour \"" . trim($_POST['theme']) . "\". Consultez l'invitation pour vérifier les infos et valider.",
                    'INVITATION', $memoire->getIdMemoire());
                $_SESSION['succes'] = "Mémoire en brouillon. Invitation envoyée à votre co-auteur. Il doit valider pour que le mémoire soit soumis à l'encadreur.";
            } else {
                MemoireRepository::supprimer($memoire->getIdMemoire());
                $_SESSION['erreur'] = "Co-auteur introuvable (matricule : $coMatricule). Opération annulée.";
                header('Location: ../View/etudiant/upload.php'); exit;
            }
        } else {
            // Solo → soumettre directement
            $pdo->prepare("UPDATE memoire SET statut='EN_ATTENTE' WHERE idMemoire=?")->execute([$memoire->getIdMemoire()]);
            $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur=?");
            $stmt->execute([trim($_POST['idProf'])]);
            $cProf = $stmt->fetchColumn();
            if ($cProf) NotificationRepository::create($cProf,
                "📝 Nouveau mémoire à valider : \"" . trim($_POST['theme']) . "\" — " . $_SESSION['prenom'] . " " . $_SESSION['nom'],
                'SOUMISSION', $memoire->getIdMemoire());
            $_SESSION['succes'] = "Mémoire soumis avec succès ! En attente de validation.";
        }

        header('Location: ../View/etudiant/dashboard.php'); exit;
    }
    public static function gererInvitation(): void {
        AuthController::requireLogin();
        if (!isset($_GET['id'])) {
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }
        $idMemoire = $_GET['id'];
        $pdo = getConnexion();

        $stmt = $pdo->prepare("SELECT * FROM co_auteur WHERE idMemoire = ? AND idCompte = ? AND statut = 'EN_ATTENTE'");
        $stmt->execute([$idMemoire, $_SESSION['idCompte']]);
        if (!$stmt->fetch()) {
            $_SESSION['erreur'] = "Invitation introuvable ou déjà traitée.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        $memoire = MemoireRepository::findById($idMemoire);
        if (!$memoire || $memoire['statut'] !== 'BROUILLON') {
            $_SESSION['erreur'] = "Ce mémoire n'est plus modifiable.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        // Récupérer le nom de l'auteur principal
        $stmt = $pdo->prepare("SELECT u.prenom, u.nom FROM memoire m JOIN compte c ON m.idCompte = c.idCompte JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur WHERE m.idMemoire = ?");
        $stmt->execute([$idMemoire]);
        $auteur = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($auteur) {
            $memoire['prenom'] = $auteur['prenom'];
            $memoire['nom'] = $auteur['nom'];
        } else {
            $memoire['prenom'] = '';
            $memoire['nom'] = '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['refuser'])) {
                MemoireRepository::supprimer($idMemoire);
                $stmt = $pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire = ?");
                $stmt->execute([$idMemoire]);
                $auteurId = $stmt->fetchColumn();
                if ($auteurId) {
                    NotificationRepository::create($auteurId,
                        "❌ " . $_SESSION['prenom'] . " " . $_SESSION['nom'] . " a refusé votre invitation. Le mémoire a été supprimé.",
                        'INVITATION', $idMemoire);
                }
                $_SESSION['succes'] = "Invitation refusée. Le mémoire a été supprimé.";
                header('Location: ../View/etudiant/dashboard.php'); exit;
            }

            $theme = trim($_POST['theme']);
            $idProf = trim($_POST['idProf']);
            $annee = trim($_POST['anneeAcademique']);
            $centre = trim($_POST['centre']);
            $fichierPdf = $memoire['fichierPdf'];

            if (isset($_FILES['fichierPdf']) && $_FILES['fichierPdf']['error'] === UPLOAD_ERR_OK) {
                $pdf = self::uploadPdf();
                if ($pdf !== false) {
                    if ($memoire['fichierPdf'] && file_exists(__DIR__.'/../uploads/memoires/'.$memoire['fichierPdf'])) {
                        unlink(__DIR__.'/../uploads/memoires/'.$memoire['fichierPdf']);
                    }
                    $fichierPdf = $pdf;
                }
            }

            $stmt = $pdo->prepare("UPDATE memoire SET theme=?, idProf=?, anneeAcademique=?, centre=?, fichierPdf=? WHERE idMemoire=?");
            $stmt->execute([$theme, $idProf, $annee, $centre, $fichierPdf, $idMemoire]);

            $pdo->prepare("UPDATE co_auteur SET statut = 'ACCEPTE' WHERE idMemoire = ? AND idCompte = ?")
                ->execute([$idMemoire, $_SESSION['idCompte']]);

            MemoireRepository::updateStatut($idMemoire, 'EN_ATTENTE');

            $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur=?");
            $stmt->execute([$idProf]);
            $cProf = $stmt->fetchColumn();
            if ($cProf) {
                NotificationRepository::create($cProf,
                    "📝 Nouveau mémoire à valider : \"$theme\" — co-auteurs : " . $_SESSION['prenom'] . " " . $_SESSION['nom'] . " et l'auteur principal",
                    'SOUMISSION', $idMemoire);
            }

            $stmt = $pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire = ?");
            $stmt->execute([$idMemoire]);
            $auteurId = $stmt->fetchColumn();
            if ($auteurId) {
                NotificationRepository::create($auteurId,
                    "✅ Votre co-auteur a accepté et soumis le mémoire. Il est maintenant en attente de validation par l'encadreur.",
                    'VALIDATION', $idMemoire);
            }

            $_SESSION['succes'] = "Mémoire accepté et soumis à l'encadreur.";
            header('Location: ../View/etudiant/dashboard.php'); exit;
        }

        $profs = Professeur::listerTous();
        $pageTitle = "Invitation à être co-auteur";
        $nbNotifs = Notification::compterNonLus($_SESSION['idCompte']);
        include __DIR__ . '/../View/etudiant/invitation.php';
        exit;
    }

    public static function repondreInvitation(): void {
        // Méthode conservée pour compatibilité, non utilisée dans le nouveau workflow
        AuthController::requireLogin();
        if (!isset($_POST['idMemoire'],$_POST['reponse'])) return;
        $idMemoire=$_POST['idMemoire'];
        $reponse  =$_POST['reponse']==='ACCEPTE'?'ACCEPTE':'REFUSE';
        MemoireRepository::repondreInvitation($idMemoire,$_SESSION['idCompte'],$reponse);

        $pdo=getConnexion();
        $mem=MemoireRepository::findById($idMemoire);
        $stmt=$pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire=?");
        $stmt->execute([$idMemoire]);
        $cAuteur=$stmt->fetchColumn();
        $msg=$reponse==='ACCEPTE'
            ? "✅ ".$_SESSION['prenom']." ".$_SESSION['nom']." a accepté votre invitation pour \"{$mem['theme']}\"."
            : "❌ ".$_SESSION['prenom']." ".$_SESSION['nom']." a refusé votre invitation pour \"{$mem['theme']}\".";
        if ($cAuteur) NotificationRepository::create($cAuteur,$msg,'INVITATION',$idMemoire);

        $_SESSION['succes']=$reponse==='ACCEPTE'?"Invitation acceptée ! Vous êtes maintenant co-auteur.":"Invitation refusée.";
        header('Location: ../View/etudiant/dashboard.php'); exit;
    }

    public static function commenter(): void {
        AuthController::requireLogin();
        if (!isset($_POST['btn_commenter'])) return;
        $idMemoire=trim($_POST['idMemoire']);
        $contenu  =trim($_POST['contenu']);
        if (empty($contenu)) { header('Location: ../View/shared/detail_memoire.php?id='.$idMemoire); exit; }

        $c=new Commentaire('',$_SESSION['idCompte'],$idMemoire,$contenu,(float)($_POST['note']??0));
        if (!$c->add()) {
            $_SESSION['erreur'] = "Erreur lors de l'ajout du commentaire.";
            header('Location: ../View/shared/detail_memoire.php?id='.$idMemoire); exit;
        }

        $pdo=getConnexion();
        $mem=MemoireRepository::findById($idMemoire);
        $nom=$_SESSION['prenom'].' '.$_SESSION['nom'];

        $stmt=$pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire=?");
        $stmt->execute([$idMemoire]);
        $cPrincipal=$stmt->fetchColumn();
        $dests=[];
        if ($cPrincipal&&$cPrincipal!==$_SESSION['idCompte']) $dests[]=$cPrincipal;
        foreach (MemoireRepository::getCoAuteurs($idMemoire) as $co)
            if ($co['statut']==='ACCEPTE'&&$co['idCompte']!==$_SESSION['idCompte']&&!in_array($co['idCompte'],$dests))
                $dests[]=$co['idCompte'];
        foreach ($dests as $d)
            NotificationRepository::create($d,"💬 $nom a commenté le mémoire \"{$mem['theme']}\"",'COMMENTAIRE',$idMemoire);

        header('Location: ../View/shared/detail_memoire.php?id='.$idMemoire); exit;
    }

    public static function liker(): void {
        AuthController::requireLogin();
        if (!isset($_POST['idMemoire'],$_POST['action_like'])) return;
        $idMemoire=$_POST['idMemoire'];
        $pdo=getConnexion();

        $stmt=$pdo->prepare("SELECT COUNT(*) FROM likes WHERE idCompte=? AND idMemoire=?");
        $stmt->execute([$_SESSION['idCompte'],$idMemoire]);
        $aLike=(bool)$stmt->fetchColumn();

        if ($aLike) {
            $pdo->prepare("DELETE FROM likes WHERE idCompte=? AND idMemoire=?")->execute([$_SESSION['idCompte'],$idMemoire]);
            $pdo->prepare("UPDATE memoire SET nbLikes=GREATEST(0,nbLikes-1) WHERE idMemoire=?")->execute([$idMemoire]);
        } else {
            $n=(int)$pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
            $pdo->prepare("INSERT IGNORE INTO likes (idLike,idCompte,idMemoire) VALUES (?,?,?)")
                ->execute(['LK'.str_pad($n+1,5,'0',STR_PAD_LEFT),$_SESSION['idCompte'],$idMemoire]);
            $pdo->prepare("UPDATE memoire SET nbLikes=nbLikes+1 WHERE idMemoire=?")->execute([$idMemoire]);

            $mem=MemoireRepository::findById($idMemoire);
            $nom=$_SESSION['prenom'].' '.$_SESSION['nom'];
            $stmt=$pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire=?");
            $stmt->execute([$idMemoire]);
            $cPrincipal=$stmt->fetchColumn();
            $dests=[];
            if ($cPrincipal&&$cPrincipal!==$_SESSION['idCompte']) $dests[]=$cPrincipal;
            foreach (MemoireRepository::getCoAuteurs($idMemoire) as $co)
                if ($co['statut']==='ACCEPTE'&&$co['idCompte']!==$_SESSION['idCompte']&&!in_array($co['idCompte'],$dests))
                    $dests[]=$co['idCompte'];
            foreach ($dests as $d)
                NotificationRepository::create($d,"❤️ $nom a aimé le mémoire \"{$mem['theme']}\"",'LIKE',$idMemoire);
        }
        header('Location: ../View/shared/detail_memoire.php?id='.$idMemoire); exit;
    }
}

// ───────────────────────────────────────────
// VALIDATION CONTROLLER
// ───────────────────────────────────────────
class ValidationController {
    public static function valider(): void {
        AuthController::requireRole('PROFESSEUR');
        if (!isset($_POST['btn_valider'])) return;
        $idMemoire=trim($_POST['idMemoire']);
        MemoireRepository::updateStatut($idMemoire,'EN_ATTENTE_DIRECTION');

        $pdo=getConnexion();
        $mem=MemoireRepository::findById($idMemoire);
        $stmt=$pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire=?");
        $stmt->execute([$idMemoire]);
        $cEtu=$stmt->fetchColumn();

        if ($cEtu) NotificationRepository::create($cEtu,
            "✅ Votre mémoire \"{$mem['theme']}\" validé par votre encadreur. En attente de la Direction.",
            'VALIDATION',$idMemoire);

        foreach (MemoireRepository::getCoAuteurs($idMemoire) as $co)
            if ($co['statut']==='ACCEPTE')
                NotificationRepository::create($co['idCompte'],
                    "✅ Le mémoire \"{$mem['theme']}\" validé par l'encadreur. En attente de la Direction.",'VALIDATION',$idMemoire);

        $stmt=$pdo->prepare("SELECT c.idCompte FROM compte c JOIN de d ON c.idUtilisateur=d.idUtilisateur");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $cDE)
            NotificationRepository::create($cDE,
                "🎓 Mémoire à publier : \"{$mem['theme']}\" — validé par ".$_SESSION['prenom']." ".$_SESSION['nom'],
                'VALIDATION',$idMemoire);

        // Désactiver upload auteur principal
        if ($cEtu) {
            $pdo->prepare("UPDATE etudiant SET peutUploader=0 WHERE idUtilisateur=(SELECT idUtilisateur FROM compte WHERE idCompte=?)")
                ->execute([$cEtu]);
        }
        // Désactiver upload co-auteurs acceptés
        foreach (MemoireRepository::getCoAuteurs($idMemoire) as $co) {
            if ($co['statut'] === 'ACCEPTE') {
                $pdo->prepare("UPDATE etudiant SET peutUploader=0 WHERE idUtilisateur = ?")
                    ->execute([$co['idUtilisateur']]);
            }
        }

        $_SESSION['succes']="Mémoire transmis à la Direction pour publication.";
        header('Location: ../View/professeur/validation.php'); exit;
    }

    public static function rejeter(): void {
        AuthController::requireRole('PROFESSEUR');
        if (!isset($_POST['btn_rejeter'])) return;
        $idMemoire  =trim($_POST['idMemoire']);
        $commentaire=trim($_POST['commentaireJury']);
        if (empty($commentaire)) {
            $_SESSION['erreur']="Un commentaire est obligatoire pour rejeter.";
            header('Location: ../View/professeur/validation.php'); exit;
        }
        MemoireRepository::updateStatut($idMemoire,'REJETE',$commentaire);

        $pdo=getConnexion();
        $mem=MemoireRepository::findById($idMemoire);
        $stmt=$pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire=?");
        $stmt->execute([$idMemoire]);
        $cEtu=$stmt->fetchColumn();

        if ($cEtu) {
            NotificationRepository::create($cEtu,
                "❌ Mémoire \"{$mem['theme']}\" rejeté. Motif : $commentaire",'REJET',$idMemoire);
            $pdo->prepare("UPDATE etudiant SET peutUploader=1 WHERE idUtilisateur=(SELECT idUtilisateur FROM compte WHERE idCompte=?)")
                ->execute([$cEtu]);
        }
        foreach (MemoireRepository::getCoAuteurs($idMemoire) as $co)
            if ($co['statut']==='ACCEPTE') {
                NotificationRepository::create($co['idCompte'],
                    "❌ Mémoire \"{$mem['theme']}\" rejeté. Motif : $commentaire",'REJET',$idMemoire);
                $pdo->prepare("UPDATE etudiant SET peutUploader=1 WHERE idUtilisateur = ?")
                    ->execute([$co['idUtilisateur']]);
            }

        $_SESSION['succes']="Mémoire rejeté. L'étudiant est notifié.";
        header('Location: ../View/professeur/validation.php'); exit;
    }

    public static function publier(): void {
        AuthController::requireLogin();
        if (!in_array($_SESSION['role'],['DE','BIBLIOTHECAIRE'])) { header('Location: ../View/auth/login.php'); exit; }
        if (!isset($_POST['btn_publier'])) return;
        $idMemoire=trim($_POST['idMemoire']);

        if ($_SESSION['role']==='BIBLIOTHECAIRE') {
            $del=MemoireRepository::getDelegationActive();
            if (!$del||$del['idBibliothecaire']!==$_SESSION['idUtilisateur']) {
                $_SESSION['erreur']="Vous n'êtes pas autorisé à publier.";
                header('Location: ../View/bibliothecaire/dashboard.php'); exit;
            }
        }

        MemoireRepository::updateStatut($idMemoire,'VALIDE');
        $pdo=getConnexion();
        $mem=MemoireRepository::findById($idMemoire);
        $stmt=$pdo->prepare("SELECT idCompte FROM memoire WHERE idMemoire=?");
        $stmt->execute([$idMemoire]);
        $cEtu=$stmt->fetchColumn();

        // Désactiver upload auteur principal
        if ($cEtu) {
            $pdo->prepare("UPDATE etudiant SET peutUploader=0 WHERE idUtilisateur=(SELECT idUtilisateur FROM compte WHERE idCompte=?)")
                ->execute([$cEtu]);
        }
        // Désactiver upload co-auteurs acceptés
        $coAuteurs = MemoireRepository::getCoAuteurs($idMemoire);
        foreach ($coAuteurs as $co) {
            if ($co['statut'] === 'ACCEPTE') {
                $pdo->prepare("UPDATE etudiant SET peutUploader=0 WHERE idUtilisateur = ?")
                    ->execute([$co['idUtilisateur']]);
            }
        }

        if ($cEtu) NotificationRepository::create($cEtu,
            "🎉 Votre mémoire \"{$mem['theme']}\" est publié ! Il est visible par tous.",'PUBLICATION',$idMemoire);
        foreach ($coAuteurs as $co)
            if ($co['statut']==='ACCEPTE')
                NotificationRepository::create($co['idCompte'],
                    "🎉 Le mémoire \"{$mem['theme']}\" est publié !",'PUBLICATION',$idMemoire);

        $_SESSION['succes']="Mémoire publié avec succès !";
        $redirect=$_SESSION['role']==='BIBLIOTHECAIRE'?'../View/bibliothecaire/dashboard.php':'../View/de/valider_memoires.php';
        header('Location: '.$redirect); exit;
    }

    public static function gererDelegation(): void {
        AuthController::requireRole('DE');
        if (isset($_POST['btn_activer_delegation'])) {
            $idBib=trim($_POST['idBibliothecaire']);
            MemoireRepository::activerDelegation($_SESSION['idUtilisateur'],$idBib);
            $_SESSION['succes']="Délégation activée. Le bibliothécaire peut maintenant publier tous les mémoires.";
        } elseif (isset($_POST['btn_desactiver_delegation'])) {
            MemoireRepository::desactiverDelegation();
            $_SESSION['succes']="Délégation désactivée.";
        }
        header('Location: ../View/de/valider_memoires.php'); exit;
    }
}

// ───────────────────────────────────────────
// ADMIN CONTROLLER (DE)
// ───────────────────────────────────────────
class AdminController {

    public static function enregistrerEtudiant(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_enregistrer_etu'])) return;
        $pdo=getConnexion();
        $id=trim($_POST['idUtilisateur']);
        $stmt=$pdo->prepare("SELECT idUtilisateur FROM utilisateur WHERE idUtilisateur=?");
        $stmt->execute([$id]);
        if ($stmt->fetch()) {
            $pdo->prepare("UPDATE utilisateur SET nom=?,prenom=?,email=? WHERE idUtilisateur=?")
                ->execute([trim($_POST['nom']),trim($_POST['prenom']),trim($_POST['email']),$id]);
            $pdo->prepare("UPDATE etudiant SET dateNaissance=?,filiere=?,option_etu=?,niveau=? WHERE idUtilisateur=?")
                ->execute([$_POST['dateNaissance']??null,trim($_POST['filiere']),trim($_POST['option_etu']),$_POST['niveau'],$id]);
        } else {
            $pdo->prepare("INSERT INTO utilisateur (idUtilisateur,nom,prenom,email,role) VALUES (?,?,?,?,'ETUDIANT')")
                ->execute([$id,trim($_POST['nom']),trim($_POST['prenom']),trim($_POST['email'])]);
            $pdo->prepare("INSERT INTO etudiant (idUtilisateur,dateNaissance,filiere,option_etu,niveau,peutUploader) VALUES (?,?,?,?,?,0)")
                ->execute([$id,$_POST['dateNaissance']??null,trim($_POST['filiere']),trim($_POST['option_etu']),$_POST['niveau']]);
        }
        $_SESSION['succes']="Étudiant enregistré.";
        header('Location: ../View/de/liste_etudiants.php'); exit;
    }

    public static function modifierEtudiant(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_modifier_etu'])) return;
        $pdo=getConnexion(); $id=trim($_POST['idUtilisateur_original']);
        $pdo->prepare("UPDATE utilisateur SET nom=?,prenom=?,email=? WHERE idUtilisateur=?")
            ->execute([trim($_POST['nom']),trim($_POST['prenom']),trim($_POST['email']),$id]);
        $pdo->prepare("UPDATE etudiant SET dateNaissance=?,filiere=?,option_etu=?,niveau=? WHERE idUtilisateur=?")
            ->execute([$_POST['dateNaissance']??null,trim($_POST['filiere']),trim($_POST['option_etu']),$_POST['niveau'],$id]);
        $_SESSION['succes']="Étudiant modifié.";
        header('Location: ../View/de/liste_etudiants.php'); exit;
    }

    public static function supprimerEtudiant(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_supprimer_etu'])) return;
        getConnexion()->prepare("DELETE FROM utilisateur WHERE idUtilisateur=?")->execute([trim($_POST['idUtilisateur'])]);
        $_SESSION['succes']="Étudiant supprimé.";
        header('Location: ../View/de/liste_etudiants.php'); exit;
    }

    public static function enregistrerProf(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_enregistrer_prof'])) return;
        $pdo=getConnexion(); $id=trim($_POST['idUtilisateur']);
        $stmt=$pdo->prepare("SELECT idUtilisateur FROM utilisateur WHERE idUtilisateur=?");
        $stmt->execute([$id]);
        if ($stmt->fetch()) {
            $pdo->prepare("UPDATE utilisateur SET nom=?,prenom=?,email=? WHERE idUtilisateur=?")
                ->execute([trim($_POST['nom']),trim($_POST['prenom']),trim($_POST['email']),$id]);
            $pdo->prepare("UPDATE professeur SET specialite=?,grade=? WHERE idUtilisateur=?")
                ->execute([trim($_POST['specialite']),trim($_POST['grade']??''),$id]);
        } else {
            $pdo->prepare("INSERT INTO utilisateur (idUtilisateur,nom,prenom,email,role) VALUES (?,?,?,?,'PROFESSEUR')")
                ->execute([$id,trim($_POST['nom']),trim($_POST['prenom']),trim($_POST['email'])]);
            $pdo->prepare("INSERT INTO professeur (idUtilisateur,specialite,grade) VALUES (?,?,?)")
                ->execute([$id,trim($_POST['specialite']),trim($_POST['grade']??'')]);
        }
        $_SESSION['succes']="Professeur enregistré.";
        header('Location: ../View/de/liste_profs.php'); exit;
    }

    public static function modifierProf(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_modifier_prof'])) return;
        $pdo=getConnexion(); $id=trim($_POST['idUtilisateur_original']);
        $pdo->prepare("UPDATE utilisateur SET nom=?,prenom=?,email=? WHERE idUtilisateur=?")
            ->execute([trim($_POST['nom']),trim($_POST['prenom']),trim($_POST['email']),$id]);
        $pdo->prepare("UPDATE professeur SET specialite=?,grade=? WHERE idUtilisateur=?")
            ->execute([trim($_POST['specialite']),trim($_POST['grade']??''),$id]);
        $_SESSION['succes']="Professeur modifié.";
        header('Location: ../View/de/liste_profs.php'); exit;
    }

    public static function supprimerProf(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_supprimer_prof'])) return;
        getConnexion()->prepare("DELETE FROM utilisateur WHERE idUtilisateur=?")->execute([trim($_POST['idUtilisateur'])]);
        $_SESSION['succes']="Professeur supprimé.";
        header('Location: ../View/de/liste_profs.php'); exit;
    }

    public static function activerUpload(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_activer_upload'])) return;
        getConnexion()->prepare("UPDATE etudiant SET peutUploader=1 WHERE idUtilisateur=?")->execute([trim($_POST['idUtilisateur'])]);
        $_SESSION['succes']="Upload activé.";
        header('Location: ../View/de/liste_etudiants.php'); exit;
    }

    public static function desactiverUpload(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_desactiver_upload'])) return;
        getConnexion()->prepare("UPDATE etudiant SET peutUploader=0 WHERE idUtilisateur=?")->execute([trim($_POST['idUtilisateur'])]);
        $_SESSION['succes']="Upload désactivé.";
        header('Location: ../View/de/liste_etudiants.php'); exit;
    }

    public static function activerUploadMasse(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_activer_upload_masse'])) return;
        $pdo=getConnexion();
        foreach ($_POST['etudiants']??[] as $id)
            $pdo->prepare("UPDATE etudiant SET peutUploader=1 WHERE idUtilisateur=?")->execute([trim($id)]);
        $_SESSION['succes']=count($_POST['etudiants']??[])." étudiant(s) activé(s).";
        header('Location: ../View/de/activation_upload.php'); exit;
    }

    public static function desactiverUploadMasse(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_desactiver_upload_masse'])) return;
        $pdo=getConnexion();
        foreach ($_POST['etudiants']??[] as $id)
            $pdo->prepare("UPDATE etudiant SET peutUploader=0 WHERE idUtilisateur=?")->execute([trim($id)]);
        $_SESSION['succes']=count($_POST['etudiants']??[])." étudiant(s) désactivé(s).";
        header('Location: ../View/de/activation_upload.php'); exit;
    }

    public static function uploaderAncien(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_upload_ancien'])) return;
        $pdo=getConnexion();
        $idEtu  =trim($_POST['idUtilisateur']);
        $idEtu2 =trim($_POST['idUtilisateur2']??'');
        $idProf =trim($_POST['idProf']);
        $theme  =trim($_POST['theme']);
        $annee  =trim($_POST['anneeAcademique']);
        $centre =trim($_POST['centre']);
        $filiere=trim($_POST['filiere']??'');
        $option =trim($_POST['option_mem']??'');

        $stmt=$pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur=?");
        $stmt->execute([$idEtu]); $idCompte=$stmt->fetchColumn();
        if (!$idCompte) {
            $idCompte='CPT_'.str_pad((int)$pdo->query("SELECT COUNT(*) FROM compte")->fetchColumn()+1,5,'0',STR_PAD_LEFT);
            $mdp=password_hash(bin2hex(random_bytes(6)),PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO compte (idCompte,idUtilisateur,username,motDePasse) VALUES (?,?,?,?)")
                ->execute([$idCompte,$idEtu,strtolower($idEtu),$mdp]);
        }

        $pdf='';
        if (isset($_FILES['fichier_pdf'])&&$_FILES['fichier_pdf']['error']===UPLOAD_ERR_OK) {
            $dir=__DIR__.'/../uploads/memoires/';
            if (!is_dir($dir)) mkdir($dir,0755,true);
            $pdf='ancien_'.$idEtu.'_'.time().'.pdf';
            move_uploaded_file($_FILES['fichier_pdf']['tmp_name'],$dir.$pdf);
        }

        $idMemoire=MemoireRepository::saveAncien($idCompte,$idProf,$theme,$annee,$centre,$filiere,$option,$pdf);

        if ($idEtu2!=='') {
            $stmt=$pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur=?");
            $stmt->execute([$idEtu2]); $idCompte2=$stmt->fetchColumn();
            if (!$idCompte2) {
                $idCompte2='CPT_'.str_pad((int)$pdo->query("SELECT COUNT(*) FROM compte")->fetchColumn()+1,5,'0',STR_PAD_LEFT);
                $mdp=password_hash(bin2hex(random_bytes(6)),PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO compte (idCompte,idUtilisateur,username,motDePasse) VALUES (?,?,?,?)")
                    ->execute([$idCompte2,$idEtu2,strtolower($idEtu2),$mdp]);
            }
            $pdo->prepare("INSERT IGNORE INTO co_auteur (idMemoire,idCompte,statut) VALUES (?,?,'ACCEPTE')")
                ->execute([$idMemoire,$idCompte2]);
        }

        $_SESSION['succes']="Ancien mémoire enregistré et publié.";
        header('Location: ../View/de/upload_ancien.php'); exit;
    }

    public static function mettreAJourVisibilite(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_mettre_a_jour_visibilite'])) return;
        $pdo=getConnexion();
        $visibles=$_POST['memoires_visibles']??[];
        $stmt=$pdo->query("SELECT idMemoire FROM memoire WHERE statut='VALIDE'");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id)
            $pdo->prepare("UPDATE memoire SET visible=? WHERE idMemoire=?")->execute([in_array($id,$visibles)?1:0,$id]);
        $_SESSION['succes']="Visibilité mise à jour.";
        header('Location: ../View/de/gestion_visibilite.php'); exit;
    }

    public static function supprimerMemoire(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_supprimer_memoire_admin'])) return;
        MemoireRepository::supprimer(trim($_POST['idMemoire']));
        $_SESSION['succes']="Mémoire supprimé.";
        header('Location: ../View/shared/liste_memoires.php'); exit;
    }

    public static function importerEtudiantsCSV(): void {
        AuthController::requireRole('DE');
        if (!isset($_POST['btn_import_etudiants'])) return;
        if (!isset($_FILES['fichier_csv'])||$_FILES['fichier_csv']['error']!==UPLOAD_ERR_OK) {
            $_SESSION['erreur']="Erreur fichier CSV."; header('Location: ../View/de/liste_etudiants.php'); exit;
        }
        $pdo=getConnexion();
        $handle=fopen($_FILES['fichier_csv']['tmp_name'],'r'); fgetcsv($handle,0,';');
        $nb=0;
        while(($row=fgetcsv($handle,0,';'))!==false) {
            if (count($row)<6) continue;
            [$id,$nom,$prenom,$email,$filiere,$option]=$row; $niveau=trim($row[6]??'L1');
            $id=trim($id);
            if (empty($id)) continue;
            $stmt=$pdo->prepare("SELECT idUtilisateur FROM utilisateur WHERE idUtilisateur=?"); $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                $pdo->prepare("INSERT INTO utilisateur (idUtilisateur,nom,prenom,email,role) VALUES (?,?,?,?,'ETUDIANT')")
                    ->execute([$id,trim($nom),trim($prenom),trim($email)]);
                $pdo->prepare("INSERT INTO etudiant (idUtilisateur,filiere,option_etu,niveau,peutUploader) VALUES (?,?,?,?,0)")
                    ->execute([$id,trim($filiere),trim($option),trim($niveau)]);
                $nb++;
            }
        }
        fclose($handle);
        $_SESSION['succes']="$nb étudiant(s) importé(s).";
        header('Location: ../View/de/liste_etudiants.php'); exit;
    }
}