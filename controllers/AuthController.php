<?php
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class AuthController {
    public function loginForm(): void {
        view('auth/login', ['titre' => 'Connexion']);
    }
    public function login(): void {
        csrf_check();
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';
        $u = Utilisateur::parEmail($email);
        if (!$u || !$u['actif'] || !password_verify($mdp, $u['mot_de_passe'])) {
            flash('erreur', 'Identifiants invalides.');
            redirect('/index.php?route=login');
        }
        start_session();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$u['id'], 'nom' => $u['nom'],
            'email' => $u['email'], 'role' => $u['role']
        ];
        redirect('/index.php?route=accueil');
    }
    public function logout(): void {
        start_session();
        $_SESSION = [];
        session_destroy();
        redirect('/index.php?route=login');
    }
}
