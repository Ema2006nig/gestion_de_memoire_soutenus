<?php
/**
 * Layout des pages d'authentification (connexion + inscription).
 * Deux colonnes : présentation à gauche, formulaire à droite.
 */
require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../shared/icons.php';

function auth_layout_open(string $title): void
{
    ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> · <?= APP_NAME ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <style>
        body { background: #0b1220; }
        .auth-wrap {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
        }
        .auth-aside {
            background:
                radial-gradient(900px 600px at 0% 0%, rgba(29,78,216,.35), transparent 60%),
                radial-gradient(700px 500px at 100% 100%, rgba(217,119,6,.20), transparent 70%),
                #0b1220;
            color: #f8fafc;
            padding: 56px;
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .auth-aside .brand {
            display: inline-flex; align-items: center; gap: 12px;
        }
        .auth-aside .brand img { width: 44px; height: 44px; border-radius: 12px; }
        .auth-aside h2 {
            font-size: 38px; font-weight: 800; letter-spacing: -.025em;
            line-height: 1.1; margin: 0 0 18px;
        }
        .auth-aside h2 .accent { color: #fbbf24; }
        .auth-aside p { color: rgba(248, 250, 252, .7); max-width: 460px; font-size: 14.5px; line-height: 1.6; }
        .auth-aside .features {
            display: grid; gap: 14px; margin-top: 30px; max-width: 420px;
        }
        .auth-aside .feat {
            display: flex; gap: 14px; align-items: flex-start;
            color: rgba(248, 250, 252, .85);
            font-size: 13.5px;
        }
        .auth-aside .feat .ico {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #fbbf24;
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .auth-aside .legal { font-size: 12px; color: rgba(248, 250, 252, .45); }

        .auth-panel {
            background: #fff;
            padding: 56px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .auth-form {
            width: 100%; max-width: 420px; margin: 0 auto;
        }
        .auth-form h1 {
            font-size: 28px; font-weight: 700; letter-spacing: -.02em;
            color: #0b1220; margin: 0 0 6px;
        }
        .auth-form .lead { color: #64748b; font-size: 14px; margin: 0 0 28px; }
        .auth-form .switch {
            margin-top: 24px; text-align: center;
            color: #64748b; font-size: 13.5px;
        }
        .auth-form .switch a {
            color: var(--brand);
            font-weight: 600;
        }
        .auth-form .switch a:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .auth-wrap { grid-template-columns: 1fr; }
            .auth-aside { display: none; }
            .auth-panel { padding: 40px 24px; }
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <aside class="auth-aside">
        <a href="<?= BASE_URL ?>/index.php" class="brand">
            <img src="<?= BASE_URL ?>/icone.jpg" alt="Logo UATM">
            <div>
                <div style="font-weight:700;font-size:15px;"><?= APP_NAME ?></div>
                <div style="font-size:11px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.1em;"><?= UNIVERSITY ?></div>
            </div>
        </a>

        <div>
            <h2>La mémoire collective<br>de l'<span class="accent">UATM Gasa</span>.</h2>
            <p>Déposez, encadrez, validez et consultez les travaux de fin d'études. Une seule plateforme pour les étudiants, les enseignants et la direction.</p>

            <div class="features">
                <div class="feat">
                    <span class="ico"><?= icon('shield', 'w-5 h-5') ?></span>
                    <div>
                        <div style="font-weight:600;color:#fff;">Accès contrôlé par la Direction</div>
                        <div style="color:rgba(255,255,255,.55);font-size:12.5px;margin-top:2px;">Seuls les comptes pré-enregistrés peuvent s'inscrire.</div>
                    </div>
                </div>
                <div class="feat">
                    <span class="ico"><?= icon('library', 'w-5 h-5') ?></span>
                    <div>
                        <div style="font-weight:600;color:#fff;">Bibliothèque numérique</div>
                        <div style="color:rgba(255,255,255,.55);font-size:12.5px;margin-top:2px;">Recherchez par filière, année, centre ou mot-clé.</div>
                    </div>
                </div>
                <div class="feat">
                    <span class="ico"><?= icon('check-double', 'w-5 h-5') ?></span>
                    <div>
                        <div style="font-weight:600;color:#fff;">Circuit de validation clair</div>
                        <div style="color:rgba(255,255,255,.55);font-size:12.5px;margin-top:2px;">Encadreur, puis Direction — chaque étape est traçable.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="legal">© <?= date('Y') ?> <?= UNIVERSITY ?> — Tous droits réservés.</div>
    </aside>

    <section class="auth-panel">
        <div class="auth-form">
<?php
}

function auth_layout_close(): void
{
    ?>
        </div>
    </section>
</div>
</body>
</html>
<?php
}

/* Affiche les messages flash (succès / erreur). */
function flash_messages(): void
{
    if (!empty($_SESSION['erreur'])) {
        echo '<div class="alert alert-danger">' . icon('alert', 'w-4 h-4')
           . '<span>' . htmlspecialchars($_SESSION['erreur']) . '</span></div>';
        unset($_SESSION['erreur']);
    }
    if (!empty($_SESSION['succes'])) {
        echo '<div class="alert alert-success">' . icon('check', 'w-4 h-4')
           . '<span>' . htmlspecialchars($_SESSION['succes']) . '</span></div>';
        unset($_SESSION['succes']);
    }
}