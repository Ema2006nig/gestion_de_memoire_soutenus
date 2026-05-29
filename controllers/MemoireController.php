<?php
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Commentaire.php';

class MemoireController {

    /* --- ETUDIANT --- */
    public function formSoumettre(): void {
        require_role(['etudiant']);
        $profs = Utilisateur::parRole('professeur');
        view('etudiant/soumettre', ['titre'=>'Soumettre un memoire','profs'=>$profs]);
    }
    public function soumettre(): void {
        $u = require_role(['etudiant']);
        csrf_check();
        $titre = trim($_POST['titre'] ?? '');
        $resume = trim($_POST['resume'] ?? '');
        $profId = (int)($_POST['professeur_id'] ?? 0);
        if ($titre === '' || $profId <= 0 || empty($_FILES['fichier']['tmp_name'])) {
            flash('erreur','Tous les champs sont requis.'); redirect('/index.php?route=soumettre');
        }
        $f = $_FILES['fichier'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 20*1024*1024) {
            flash('erreur','Fichier invalide (max 20Mo).'); redirect('/index.php?route=soumettre');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($f['tmp_name']);
        if ($mime !== 'application/pdf') {
            flash('erreur','Seuls les PDF sont acceptes.'); redirect('/index.php?route=soumettre');
        }
        $nom = bin2hex(random_bytes(8)) . '.pdf';
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
        move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $nom);

        $id = Memoire::creer([
            'etudiant_id'=>$u['id'],'professeur_id'=>$profId,
            'titre'=>$titre,'resume'=>$resume,'fichier'=>$nom
        ]);
        // Notifications
        Notification::envoyer($u['id'],
            "Votre memoire \"$titre\" a ete depose.",
            'Depot recu', "Bonjour {$u['nom']},\n\nVotre memoire \"$titre\" a bien ete recu.\n");
        Notification::envoyer($profId,
            "Nouveau memoire a valider : $titre",
            'Nouveau depot', "Un etudiant a depose un memoire intitule \"$titre\".");
        flash('succes','Memoire soumis avec succes.');
        redirect('/index.php?route=mes_memoires');
    }
    public function mesMemoires(): void {
        $u = require_role(['etudiant']);
        $liste = Memoire::parEtudiant($u['id']);
        view('etudiant/liste', ['titre'=>'Mes memoires','liste'=>$liste]);
    }

    /* --- PROFESSEUR --- */
    public function aValider(): void {
        $u = require_role(['professeur']);
        $liste = Memoire::parProfesseur($u['id']);
        view('professeur/liste', ['titre'=>'Memoires a encadrer','liste'=>$liste]);
    }
    public function decider(): void {
        $u = require_role(['professeur']);
        csrf_check();
        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $m = Memoire::parId($id);
        if (!$m || (int)$m['professeur_id'] !== $u['id']) { http_response_code(403); die('Refus.'); }
        $statut = $action === 'valider' ? 'valide_prof' : 'refuse_prof';
        Memoire::changerStatut($id, $statut);
        $msg = $statut === 'valide_prof' ? 'valide' : 'refuse';
        Notification::envoyer((int)$m['etudiant_id'],
            "Votre memoire a ete $msg par le professeur.",
            "Memoire $msg",
            "Votre memoire \"{$m['titre']}\" a ete $msg par le professeur.");
        flash('succes',"Decision enregistree.");
        redirect('/index.php?route=prof_liste');
    }

    /* --- DIRECTION --- */
    public function superviser(): void {
        require_role(['direction']);
        $liste = Memoire::tous();
        $stats = Memoire::statistiques();
        view('direction/liste', ['titre'=>'Supervision','liste'=>$liste,'stats'=>$stats]);
    }
    public function validerAdmin(): void {
        require_role(['direction']);
        csrf_check();
        $id = (int)($_POST['id'] ?? 0);
        $m = Memoire::parId($id);
        if (!$m) { http_response_code(404); die('Introuvable.'); }
        if ($m['statut'] !== 'valide_prof') {
            flash('erreur','Le memoire doit etre valide par le professeur.');
            redirect('/index.php?route=direction');
        }
        Memoire::changerStatut($id, 'en_ligne');
        Notification::envoyer((int)$m['etudiant_id'],
            "Votre memoire est valide et mis en ligne.",
            'Approbation finale',
            "Felicitations, votre memoire \"{$m['titre']}\" est desormais en ligne.");
        flash('succes','Memoire mis en ligne.');
        redirect('/index.php?route=direction');
    }

    /* --- CONSULTATION / LECTURE --- */
    public function bibliotheque(): void {
        require_login();
        $q = trim($_GET['q'] ?? '');
        $liste = Memoire::enLigne($q ?: null);
        view('memoire/bibliotheque', ['titre'=>'Bibliotheque','liste'=>$liste,'q'=>$q]);
    }
    public function lire(): void {
        $u = require_login();
        $id = (int)($_GET['id'] ?? 0);
        $m = Memoire::parId($id);
        if (!$m) { http_response_code(404); die('Introuvable.'); }
        // Acces : etudiant proprietaire, prof encadrant, direction, commentateur si en_ligne
        $autorise = match ($u['role']) {
            'etudiant'      => (int)$m['etudiant_id'] === $u['id'],
            'professeur'    => (int)$m['professeur_id'] === $u['id'],
            'direction', 'service_technique' => true,
            'commentateur'  => $m['statut'] === 'en_ligne',
            default => false,
        };
        if (!$autorise) { http_response_code(403); die('Acces refuse.'); }
        $coms = Commentaire::parMemoire($id);
        view('memoire/lire', ['titre'=>$m['titre'],'m'=>$m,'coms'=>$coms]);
    }
    public function pdf(): void {
        // Sert le PDF inline (jamais en piece jointe) avec verification d'acces
        $u = require_login();
        $id = (int)($_GET['id'] ?? 0);
        $m = Memoire::parId($id);
        if (!$m) { http_response_code(404); die('Introuvable.'); }
        $autorise = match ($u['role']) {
            'etudiant' => (int)$m['etudiant_id'] === $u['id'],
            'professeur' => (int)$m['professeur_id'] === $u['id'],
            'direction','service_technique' => true,
            'commentateur' => $m['statut'] === 'en_ligne',
            default => false,
        };
        if (!$autorise) { http_response_code(403); die('Refus.'); }
        $chemin = UPLOAD_DIR . '/' . basename($m['fichier']);
        if (!is_file($chemin)) { http_response_code(404); die('Fichier manquant.'); }
        // En-tetes anti-telechargement / anti-cache (la protection totale n'existe pas,
        // mais on bloque telechargement direct, indexation et mise en cache).
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="memoire.pdf"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        header('X-Frame-Options: SAMEORIGIN');
        readfile($chemin);
        exit;
    }
    public function commenter(): void {
        $u = require_role(['professeur','commentateur','direction']);
        csrf_check();
        $id = (int)($_POST['memoire_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');
        $m = Memoire::parId($id);
        if (!$m || $contenu === '') { flash('erreur','Commentaire invalide.'); redirect('/index.php?route=lire&id='.$id); }
        // Professeur : seulement son memoire ; commentateur : seulement si en_ligne
        if ($u['role'] === 'professeur' && (int)$m['professeur_id'] !== $u['id']) { http_response_code(403); die('Refus.'); }
        if ($u['role'] === 'commentateur' && $m['statut'] !== 'en_ligne') { http_response_code(403); die('Refus.'); }
        Commentaire::creer($id, $u['id'], $contenu);
        Notification::envoyer((int)$m['etudiant_id'],
            "Nouveau commentaire sur votre memoire.",
            'Nouveau commentaire',
            "Un commentaire a ete ajoute sur \"{$m['titre']}\" par {$u['nom']}.");
        flash('succes','Commentaire ajoute.');
        redirect('/index.php?route=lire&id=' . $id);
    }
}
