<?php
/**
 * Layout des pages d'authentification (connexion + inscription).
 * Split-screen Tailwind : présentation à gauche, formulaire à droite.
 */
require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../shared/icons.php';

if (!function_exists('flash_messages')) {
    function flash_messages(): void {
        if (!empty($_SESSION['erreur'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['erreur']) . '</div>';
            unset($_SESSION['erreur']);
        }
        if (!empty($_SESSION['succes'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['succes']) . '</div>';
            unset($_SESSION['succes']);
        }
        if (!empty($_SESSION['info'])) {
            echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info']) . '</div>';
            unset($_SESSION['info']);
        }
    }
}

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
    <?php require __DIR__ . '/../shared/theme.php'; ?>
</head>
<body class="bg-[#0b1220] text-ink antialiased min-h-screen">

<div class="auth-wrap min-h-screen grid lg:grid-cols-[1.05fr_1fr]">

    <!-- ============ PANNEAU GAUCHE (présentation) ============ -->
    <aside class="auth-aside hidden lg:flex flex-col justify-between p-14 relative overflow-hidden text-slate-100"
           style="background:
             radial-gradient(900px 600px at 0% 0%, rgba(29,78,216,.35), transparent 60%),
             radial-gradient(700px 500px at 100% 100%, rgba(217,119,6,.22), transparent 70%),
             #0b1220;">
        <a href="<?= BASE_URL ?>/index.php" class="inline-flex items-center gap-3 relative z-10">
            <img src="<?= BASE_URL ?>/icone.jpg" alt="Logo UATM" class="w-11 h-11 rounded-xl ring-1 ring-white/15">
            <div>
                <div class="font-bold text-[15px]"><?= APP_NAME ?></div>
                <div class="text-[11px] text-white/60 uppercase tracking-widest font-bold mt-0.5"><?= UNIVERSITY ?></div>
            </div>
        </a>

        <div class="relative z-10 max-w-md">
            <h2 class="text-[38px] xl:text-[44px] font-extrabold leading-[1.05] tracking-tight m-0 mb-5">
                La mémoire collective<br>
                de l'<span class="text-accent-400">UATM Gasa</span>.
            </h2>
            <p class="text-white/70 text-[15px] leading-relaxed max-w-md">
                Déposez, encadrez, validez et consultez les travaux de fin d'études.
                Une seule plateforme pour les étudiants, les professeurs, la Direction
                et la bibliothèque.
            </p>

            <div class="grid gap-4 mt-8 max-w-md">
                <div class="flex gap-3.5 items-start text-white/85 text-[13.5px]">
                    <span class="bg-white/8 border border-white/15 text-accent-400 w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                        <?= icon('library', 'w-5 h-5') ?>
                    </span>
                    <div>
                        <div class="font-semibold text-white">Bibliothèque structurée</div>
                        <div class="text-white/55 text-[12.5px] mt-0.5">Filière, année, centre, mot-clé.</div>
                    </div>
                </div>
                <div class="flex gap-3.5 items-start text-white/85 text-[13.5px]">
                    <span class="bg-white/8 border border-white/15 text-accent-400 w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                        <?= icon('check-double', 'w-5 h-5') ?>
                    </span>
                    <div>
                        <div class="font-semibold text-white">Workflow de validation</div>
                        <div class="text-white/55 text-[12.5px] mt-0.5">Encadreur, Direction, Bibliothèque.</div>
                    </div>
                </div>
                <div class="flex gap-3.5 items-start text-white/85 text-[13.5px]">
                    <span class="bg-white/8 border border-white/15 text-accent-400 w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                        <?= icon('lock', 'w-5 h-5') ?>
                    </span>
                    <div>
                        <div class="font-semibold text-white">Comptes pré-enregistrés</div>
                        <div class="text-white/55 text-[12.5px] mt-0.5">Accès réservé à la communauté UATM.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-white/45 text-[12px] relative z-10">
            © <?= date('Y') ?> · <?= UNIVERSITY ?>
        </div>
    </aside>

    <!-- ============ PANNEAU DROITE (formulaire) ============ -->
    <section class="auth-panel bg-white flex flex-col justify-center px-6 md:px-10 lg:px-14 py-12">
        <div class="auth-form w-full max-w-md mx-auto">
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
