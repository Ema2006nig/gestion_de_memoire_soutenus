<?php
/**
 * Page d'accueil publique.
 * Si l'utilisateur est déjà connecté, redirection automatique vers son
 * tableau de bord.
 */
session_start();
require_once __DIR__ . '/Config/connexion.php';
require_once __DIR__ . '/Config/config.php';
require_once __DIR__ . '/View/shared/icons.php';

if (isset($_SESSION['idCompte'])) {
    $dashboards = [
        'DE'             => 'View/de/dashboard.php',
        'PROFESSEUR'     => 'View/professeur/dashboard.php',
        'BIBLIOTHECAIRE' => 'View/bibliothecaire/dashboard.php',
        'ETUDIANT'       => 'View/etudiant/dashboard.php',
    ];
    header('Location: ' . ($dashboards[$_SESSION['role']] ?? 'View/etudiant/dashboard.php'));
    exit;
}

$pdo = getConnexion();
$stats = [
    'memoires'    => (int) $pdo->query("SELECT COUNT(*) FROM memoire WHERE statut = 'VALIDE'")->fetchColumn(),
    'etudiants'   => (int) $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn(),
    'professeurs' => (int) $pdo->query("SELECT COUNT(*) FROM professeur")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?> — <?= UNIVERSITY ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <style>
        body { background: #fafbfd; }
        .landing-head {
            background: #fff;
            border-bottom: 1px solid var(--line);
        }
        .landing-head .inner {
            max-width: 1180px; margin: 0 auto;
            padding: 20px 32px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .landing-head .brand { display: inline-flex; align-items: center; gap: 12px; }
        .landing-head .brand img { width: 42px; height: 42px; border-radius: 12px; }
        .landing-head .brand .name { font-weight: 700; font-size: 15px; color: var(--ink); }
        .landing-head .brand .sub { font-size: 11px; text-transform: uppercase;
                                    letter-spacing: .08em; color: var(--muted); margin-top: 2px; }
        .landing-head nav { display: flex; gap: 10px; align-items: center; }

        .hero {
            max-width: 1180px; margin: 0 auto;
            padding: 72px 32px 56px;
            display: grid; grid-template-columns: 1.1fr .9fr; gap: 56px; align-items: center;
        }
        .hero .eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--brand-soft); color: var(--brand-strong);
            font-size: 11.5px; font-weight: 700;
            padding: 6px 14px; border-radius: 999px;
            text-transform: uppercase; letter-spacing: .1em;
        }
        .hero h1 {
            margin: 18px 0 16px; font-size: 52px;
            font-weight: 800; letter-spacing: -.03em;
            line-height: 1.05; color: var(--ink);
        }
        .hero h1 .accent { color: var(--accent); }
        .hero .lead { color: var(--ink-soft); font-size: 16px; line-height: 1.6; max-width: 540px; }
        .hero .cta { display: flex; gap: 12px; margin-top: 28px; flex-wrap: wrap; }
        .hero .figures {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 28px; margin-top: 44px; max-width: 460px;
        }
        .hero .figures .n {
            font-size: 30px; font-weight: 800; color: var(--ink); letter-spacing: -.02em;
        }
        .hero .figures .l {
            font-size: 11px; color: var(--muted);
            text-transform: uppercase; letter-spacing: .08em; font-weight: 600; margin-top: 2px;
        }

        .hero .visual {
            background: linear-gradient(165deg, #0b1220, #1e3a8a 80%);
            color: #fff;
            padding: 28px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            position: relative; overflow: hidden;
        }
        .hero .visual::before {
            content: ''; position: absolute; top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: rgba(217, 119, 6, .2); border-radius: 50%; filter: blur(40px);
        }
        .hero .visual .row {
            display: flex; align-items: center; gap: 14px;
            padding: 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .06);
            margin-bottom: 10px; position: relative;
        }
        .hero .visual .row .pic {
            width: 38px; height: 38px; border-radius: 10px;
            background: rgba(251, 191, 36, .18); color: #fbbf24;
            display: flex; align-items: center; justify-content: center;
        }
        .hero .visual .row .t { font-size: 13.5px; font-weight: 600; color: #fff; }
        .hero .visual .row .s { font-size: 11.5px; color: rgba(255, 255, 255, .6); margin-top: 2px; }
        .hero .visual .head {
            display: flex; justify-content: space-between; align-items: center;
            padding-bottom: 16px; margin-bottom: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }
        .hero .visual .head .label {
            font-size: 11px; font-weight: 700; color: #fbbf24;
            text-transform: uppercase; letter-spacing: .12em;
        }

        footer.landing {
            border-top: 1px solid var(--line);
            padding: 22px 32px;
            color: var(--muted);
            font-size: 12.5px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .hero { grid-template-columns: 1fr; padding: 48px 24px; gap: 32px; }
            .hero h1 { font-size: 38px; }
        }
    </style>
</head>
<body>
<header class="landing-head">
    <div class="inner">
        <a href="#" class="brand">
            <img src="<?= BASE_URL ?>/icone.jpg" alt="Logo <?= UNIVERSITY ?>">
            <div>
                <div class="name"><?= APP_NAME ?></div>
                <div class="sub"><?= UNIVERSITY ?></div>
            </div>
        </a>
        <nav>
            <a href="View/auth/login.php" class="btn btn-ghost btn-sm">Connexion</a>
            <a href="View/auth/register.php" class="btn btn-primary btn-sm">
                Créer un compte <?= icon('arrow-right', 'w-4 h-4') ?>
            </a>
        </nav>
    </div>
</header>

<main class="hero">
    <div>
        <span class="eyebrow"><?= icon('sparkles', 'w-3.5 h-3.5') ?> Plateforme officielle</span>
        <h1>La bibliothèque numérique des <span class="accent">mémoires soutenus</span>.</h1>
        <p class="lead">
            Consultez, déposez et faites valider les travaux de fin d'études de
            l'<?= UNIVERSITY ?>. Une seule plateforme pour les étudiants,
            les enseignants et la Direction.
        </p>

        <div class="cta">
            <a href="View/auth/login.php" class="btn btn-primary">
                <?= icon('arrow-right', 'w-4 h-4') ?> Se connecter
            </a>
            <a href="View/auth/register.php" class="btn btn-ghost">
                <?= icon('user', 'w-4 h-4') ?> Activer mon compte
            </a>
        </div>

        <div class="figures">
            <div><div class="n"><?= $stats['memoires'] ?></div><div class="l">Mémoires publiés</div></div>
            <div><div class="n"><?= $stats['etudiants'] ?></div><div class="l">Étudiants</div></div>
            <div><div class="n"><?= $stats['professeurs'] ?></div><div class="l">Encadreurs</div></div>
        </div>
    </div>

    <aside class="visual" aria-hidden="true">
        <div class="head">
            <span class="label">Dernière publication</span>
            <span style="font-size:11px;color:rgba(255,255,255,.55);">Mise à jour quotidienne</span>
        </div>
        <div class="row">
            <span class="pic"><?= icon('library', 'w-5 h-5') ?></span>
            <div>
                <div class="t">Bibliothèque structurée</div>
                <div class="s">Filière · année · centre · mot-clé</div>
            </div>
        </div>
        <div class="row">
            <span class="pic"><?= icon('check-double', 'w-5 h-5') ?></span>
            <div>
                <div class="t">Double validation</div>
                <div class="s">Encadreur puis Direction</div>
            </div>
        </div>
        <div class="row">
            <span class="pic"><?= icon('shield', 'w-5 h-5') ?></span>
            <div>
                <div class="t">Lecture sécurisée</div>
                <div class="s">PDF en ligne, sans téléchargement</div>
            </div>
        </div>
        <div class="row">
            <span class="pic"><?= icon('message', 'w-5 h-5') ?></span>
            <div>
                <div class="t">Échanges entre étudiants</div>
                <div class="s">Commentaires, notes, mentions</div>
            </div>
        </div>
    </aside>
</main>

<footer class="landing">
    © <?= date('Y') ?> <?= UNIVERSITY ?> — Tous droits réservés.
</footer>
</body>
</html>
