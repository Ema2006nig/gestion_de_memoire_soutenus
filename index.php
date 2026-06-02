<?php
/**
 * Page d'accueil publique. Redirige vers le dashboard si l'utilisateur
 * est déjà connecté. Logique métier inchangée — markup refondu en Tailwind.
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
    <?php require __DIR__ . '/View/shared/theme.php'; ?>
</head>
<body class="bg-slate-50 text-ink antialiased min-h-screen flex flex-col">

<!-- ============ HEADER ============ -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-20">
    <div class="max-w-7xl mx-auto px-5 md:px-8 py-4 flex items-center justify-between gap-4">
        <a href="#" class="inline-flex items-center gap-3">
            <img src="<?= BASE_URL ?>/icone.jpg" alt="Logo <?= UNIVERSITY ?>" class="w-11 h-11 rounded-xl object-cover ring-1 ring-slate-200">
            <div class="hidden sm:block">
                <div class="font-bold text-[15px] text-ink leading-tight"><?= APP_NAME ?></div>
                <div class="text-[10.5px] uppercase tracking-widest text-slate-500 font-bold mt-0.5"><?= UNIVERSITY ?></div>
            </div>
        </a>
        <nav class="flex gap-2 items-center">
            <a href="View/auth/login.php" class="btn btn-ghost btn-sm">Connexion</a>
            <a href="View/auth/register.php" class="btn btn-primary btn-sm">
                Créer un compte <?= icon('arrow-right', 'w-4 h-4') ?>
            </a>
        </nav>
    </div>
</header>

<!-- ============ HERO ============ -->
<main class="flex-1">
    <section class="max-w-7xl mx-auto px-5 md:px-8 py-14 md:py-20 grid lg:grid-cols-[1.1fr_0.9fr] gap-10 lg:gap-14 items-center">
        <div>
            <span class="inline-flex items-center gap-1.5 bg-brand-100 text-brand-700 text-[11.5px] font-bold px-3.5 py-1.5 rounded-full uppercase tracking-widest">
                <?= icon('sparkles', 'w-3.5 h-3.5') ?> Plateforme officielle
            </span>
            <h1 class="mt-5 mb-4 text-4xl md:text-5xl lg:text-[52px] font-extrabold tracking-tight leading-[1.05] text-ink">
                La bibliothèque numérique des
                <span class="text-accent-600">mémoires soutenus</span>.
            </h1>
            <p class="text-ink-soft text-base md:text-[17px] leading-relaxed max-w-2xl">
                Consultez, déposez et faites valider les travaux de fin d'études de
                l'<?= UNIVERSITY ?>. Une seule plateforme pour les étudiants,
                les enseignants et la Direction.
            </p>

            <div class="flex gap-3 mt-7 flex-wrap">
                <a href="View/auth/login.php" class="btn btn-primary">
                    <?= icon('arrow-right', 'w-4 h-4') ?> Se connecter
                </a>
                <a href="View/auth/register.php" class="btn btn-ghost">
                    <?= icon('user', 'w-4 h-4') ?> Activer mon compte
                </a>
            </div>

            <div class="grid grid-cols-3 gap-6 md:gap-8 mt-11 max-w-md">
                <div>
                    <div class="text-2xl md:text-[30px] font-extrabold text-ink tracking-tight leading-none"><?= $stats['memoires'] ?></div>
                    <div class="text-[11px] text-slate-500 uppercase tracking-widest font-bold mt-1.5">Mémoires publiés</div>
                </div>
                <div>
                    <div class="text-2xl md:text-[30px] font-extrabold text-ink tracking-tight leading-none"><?= $stats['etudiants'] ?></div>
                    <div class="text-[11px] text-slate-500 uppercase tracking-widest font-bold mt-1.5">Étudiants</div>
                </div>
                <div>
                    <div class="text-2xl md:text-[30px] font-extrabold text-ink tracking-tight leading-none"><?= $stats['professeurs'] ?></div>
                    <div class="text-[11px] text-slate-500 uppercase tracking-widest font-bold mt-1.5">Encadreurs</div>
                </div>
            </div>
        </div>

        <!-- Visuel décoratif -->
        <aside class="relative rounded-3xl p-7 text-white shadow-pop overflow-hidden"
               style="background: linear-gradient(165deg, #0b1220, #1e3a8a 80%);"
               aria-hidden="true">
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full blur-3xl"
                 style="background: rgba(217,119,6,.3);"></div>

            <div class="relative flex justify-between items-center pb-4 mb-3.5 border-b border-white/10">
                <span class="text-[11px] font-bold text-accent-400 uppercase tracking-widest">Aperçu plateforme</span>
                <span class="text-[11px] text-white/55">Temps réel</span>
            </div>

            <div class="relative space-y-2.5">
                <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white/5 border border-white/5">
                    <span class="w-10 h-10 rounded-xl bg-accent-400/20 text-accent-400 flex items-center justify-center shrink-0">
                        <?= icon('library', 'w-5 h-5') ?>
                    </span>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-semibold text-white">Bibliothèque structurée</div>
                        <div class="text-[11.5px] text-white/55 mt-0.5">Filière · année · centre · mot-clé</div>
                    </div>
                </div>

                <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white/5 border border-white/5">
                    <span class="w-10 h-10 rounded-xl bg-accent-400/20 text-accent-400 flex items-center justify-center shrink-0">
                        <?= icon('check-double', 'w-5 h-5') ?>
                    </span>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-semibold text-white">Workflow de validation</div>
                        <div class="text-[11.5px] text-white/55 mt-0.5">Prof · Direction · Bibliothèque</div>
                    </div>
                </div>

                <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white/5 border border-white/5">
                    <span class="w-10 h-10 rounded-xl bg-accent-400/20 text-accent-400 flex items-center justify-center shrink-0">
                        <?= icon('upload', 'w-5 h-5') ?>
                    </span>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-semibold text-white">Dépôt sécurisé PDF</div>
                        <div class="text-[11.5px] text-white/55 mt-0.5">Mots-clés, résumé, co-auteurs</div>
                    </div>
                </div>
            </div>
        </aside>
    </section>
</main>

<!-- ============ FOOTER ============ -->
<footer class="border-t border-slate-200 py-6 px-5 md:px-8 text-center text-slate-500 text-[12.5px]">
    © <?= date('Y') ?> · <?= UNIVERSITY ?> — Plateforme des mémoires soutenus.
</footer>

</body>
</html>
