<?php
// Point d'entree unique (Front Controller)
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/MemoireController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// En-tetes de securite
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; object-src 'self'");

start_session();
$route = $_GET['route'] ?? 'accueil';

$auth   = new AuthController();
$mem    = new MemoireController();
$admin  = new AdminController();

switch ($route) {
    case 'login':            $_SERVER['REQUEST_METHOD']==='POST' ? $auth->login()   : $auth->loginForm(); break;
    case 'logout':           $auth->logout(); break;

    case 'accueil':          $mem->bibliotheque(); break;
    case 'lire':             $mem->lire(); break;
    case 'pdf':              $mem->pdf(); break;
    case 'commenter':        $mem->commenter(); break;

    case 'soumettre':        $_SERVER['REQUEST_METHOD']==='POST' ? $mem->soumettre() : $mem->formSoumettre(); break;
    case 'mes_memoires':     $mem->mesMemoires(); break;

    case 'prof_liste':       $mem->aValider(); break;
    case 'prof_decider':     $mem->decider(); break;

    case 'direction':        $mem->superviser(); break;
    case 'direction_valider':$mem->validerAdmin(); break;

    case 'admin_users':      $_SERVER['REQUEST_METHOD']==='POST' ? $admin->creerUtilisateur() : $admin->utilisateurs(); break;
    case 'admin_toggle':     $admin->basculer(); break;

    default: http_response_code(404); echo 'Page introuvable.';
}
