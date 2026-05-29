<?php
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class AdminController {
    public function utilisateurs(): void {
        require_role(['service_technique']);
        $liste = Utilisateur::tous();
        view('direction/utilisateurs', ['titre'=>'Utilisateurs','liste'=>$liste]);
    }
    public function creerUtilisateur(): void {
        require_role(['service_technique']);
        csrf_check();
        $data = [
            'nom'=>trim($_POST['nom'] ?? ''),
            'email'=>trim($_POST['email'] ?? ''),
            'mot_de_passe'=>$_POST['mot_de_passe'] ?? '',
            'role'=>$_POST['role'] ?? 'etudiant',
            'matricule'=>$_POST['matricule'] ?? null,
            'grade'=>$_POST['grade'] ?? null,
        ];
        if ($data['nom']==='' || !filter_var($data['email'],FILTER_VALIDATE_EMAIL) || strlen($data['mot_de_passe'])<8) {
            flash('erreur','Champs invalides (mot de passe min 8).'); redirect('/index.php?route=admin_users');
        }
        if (Utilisateur::parEmail($data['email'])) {
            flash('erreur','Email deja utilise.'); redirect('/index.php?route=admin_users');
        }
        Utilisateur::creer($data);
        flash('succes','Utilisateur cree.');
        redirect('/index.php?route=admin_users');
    }
    public function basculer(): void {
        require_role(['service_technique']);
        csrf_check();
        Utilisateur::basculerActif((int)($_POST['id'] ?? 0));
        flash('succes','Statut modifie.');
        redirect('/index.php?route=admin_users');
    }
}
