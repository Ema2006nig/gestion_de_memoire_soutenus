<?php
/**
 * Layout principal — sidebar + topbar + content.
 * Markup refondu en Tailwind CSS, logique métier inchangée.
 *
 * Usage :
 *   $pageTitle    = "Tableau de bord";
 *   $pageSection  = "Espace étudiant";
 *   $activeRoute  = "dashboard";
 *   require_once __DIR__ . '/../shared/layout.php';
 *   layout_open();
 *   ?> ... contenu ... <?php
 *   layout_close();
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../Config/connexion.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/../../classes/Notification.php';

/* Variables exposées à toutes les vues (compat ascendante). */
$idCompte      = $_SESSION['idCompte']      ?? '';
$idUtilisateur = $_SESSION['idUtilisateur'] ?? '';
$nom           = $_SESSION['nom']           ?? '';
$prenom        = $_SESSION['prenom']        ?? '';
$email         = $_SESSION['email']         ?? '';
$role          = $_SESSION['role']          ?? '';
$nbNotifs      = $idCompte ? Notification::compterNonLus($idCompte) : 0;

function build_navigation(string $role): array
{
    $base = '/View';

    $menu = [
        'main' => [
            ['key'=>'dashboard',    'label'=>'Tableau de bord', 'icon'=>'home',
             'url' => $base . '/' . strtolower($role) . '/dashboard.php'],
            ['key'=>'memoires',     'label'=>'Bibliothèque',    'icon'=>'library',
             'url' => $base . '/shared/liste_memoires.php'],
            ['key'=>'notifications','label'=>'Notifications',   'icon'=>'bell',
             'url' => $base . '/shared/notifications.php', 'badge' => true],
        ],
        'role' => [],
    ];

    if ($role === 'DE') {
        $menu['main'][0]['url'] = $base . '/de/dashboard.php';
        $menu['role'] = [
            ['key'=>'etudiants',  'label'=>'Étudiants',         'icon'=>'users',         'url' => $base . '/de/liste_etudiants.php'],
            ['key'=>'professeurs','label'=>'Professeurs',       'icon'=>'cap',           'url' => $base . '/de/liste_profs.php'],
            ['key'=>'valider',    'label'=>'Validation finale', 'icon'=>'check-double',  'url' => $base . '/de/valider_memoires.php'],
            ['key'=>'visibilite', 'label'=>'Visibilité',        'icon'=>'eye',           'url' => $base . '/de/gestion_visibilite.php'],
            ['key'=>'imports',    'label'=>'Imports & archives','icon'=>'upload',        'url' => $base . '/de/upload_ancien.php'],
        ];
    }

    if ($role === 'PROFESSEUR') {
        $menu['main'][0]['url'] = $base . '/professeur/dashboard.php';
        $menu['role'] = [
            ['key'=>'valider', 'label'=>'À valider', 'icon'=>'clipboard', 'url' => $base . '/professeur/validation.php'],
        ];
    }

    if ($role === 'BIBLIOTHECAIRE') {
        $menu['main'][0]['url'] = $base . '/bibliothecaire/dashboard.php';
        $menu['role'] = [
            ['key'=>'publier', 'label'=>'À publier', 'icon'=>'send', 'url' => $base . '/bibliothecaire/dashboard.php'],
        ];
    }

    if ($role === 'ETUDIANT') {
        $menu['main'][0]['url'] = $base . '/etudiant/dashboard.php';
        $peutUploader = false;
        if (!empty($_SESSION['idUtilisateur'])) {
            $stmt = getConnexion()->prepare("SELECT peutUploader FROM etudiant WHERE idUtilisateur = ?");
            $stmt->execute([$_SESSION['idUtilisateur']]);
            $peutUploader = (bool) $stmt->fetchColumn();
        }
        if ($peutUploader) {
            $menu['role'][] = ['key'=>'upload', 'label'=>'Déposer mon mémoire', 'icon'=>'upload', 'url' => $base . '/etudiant/upload.php'];
        }
    }

    $profilUrl = $base . '/' . strtolower($role) . '/profil.php';
    $menu['role'][] = ['key'=>'profil', 'label'=>'Mon profil', 'icon'=>'user', 'url' => $profilUrl];

    return $menu;
}

function current_user(): array
{
    return [
        'id'        => $_SESSION['idCompte']      ?? '',
        'matricule' => $_SESSION['idUtilisateur'] ?? '',
        'nom'       => $_SESSION['nom']           ?? '',
        'prenom'    => $_SESSION['prenom']        ?? '',
        'email'     => $_SESSION['email']         ?? '',
        'role'      => $_SESSION['role']          ?? '',
    ];
}

function role_label(string $role): string
{
    return match ($role) {
        'DE'             => 'Direction des Études',
        'PROFESSEUR'     => 'Professeur',
        'BIBLIOTHECAIRE' => 'Bibliothécaire',
        'ETUDIANT'       => 'Étudiant',
        default          => 'Utilisateur',
    };
}

function layout_open(): void
{
    global $pageTitle, $pageSection, $activeRoute;

    $user      = current_user();
    $nbNotifs  = $user['id'] ? Notification::compterNonLus($user['id']) : 0;
    $menu      = build_navigation($user['role']);
    $initials  = strtoupper(mb_substr($user['prenom'], 0, 1) . mb_substr($user['nom'], 0, 1));
    $title     = $pageTitle   ?? 'Mémoires Soutenus';
    $section   = $pageSection ?? role_label($user['role']);
    $active    = $activeRoute ?? '';
    $logout    = BASE_URL . '/Controller/traitement_auth.php?action=logout';
    ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> · <?= APP_NAME ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/icone.jpg">
    <!-- FontAwesome 6 (CDN officiel) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <?php require __DIR__ . '/theme.php'; ?>
    <script src="<?= ASSETS_URL ?>/js/app.js" defer></script>
</head>
<body class="bg-slate-50 text-ink">

<div class="app grid min-h-screen" style="grid-template-columns: 256px 1fr;" id="app">

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar bg-[#0b1220] text-slate-300 px-3 py-5 flex flex-col gap-0.5 sticky top-0 h-screen overflow-y-auto border-r border-white/5">
        <a href="<?= BASE_URL ?>/index.php" class="sidebar-brand flex items-center gap-3 px-2.5 pb-5 mb-3 border-b border-white/5">
            <img src="<?= BASE_URL ?>/icone.jpg" alt="UATM" class="w-10 h-10 rounded-xl object-cover ring-1 ring-white/10">
            <div>
                <div class="text-white font-bold text-sm tracking-tight leading-tight"><?= APP_NAME ?></div>
                <div class="text-slate-400 text-[10.5px] uppercase tracking-widest font-bold mt-0.5">UATM · Gasa</div>
            </div>
        </a>

        <div class="sidebar-section text-slate-500 text-[10px] uppercase tracking-widest px-3.5 pt-4 pb-2 font-bold">
            Navigation
        </div>
        <?php foreach ($menu['main'] as $item):
            $isActive = ($active === $item['key']) ? ' is-active' : '';
            $badge    = (!empty($item['badge']) && $nbNotifs > 0)
                      ? '<span class="nav-badge">' . $nbNotifs . '</span>' : '';
        ?>
            <a href="<?= BASE_URL . $item['url'] ?>" class="nav-item<?= $isActive ?>">
                <span class="nav-icon"><?= icon($item['icon'], 'w-5 h-5') ?></span>
                <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                <?= $badge ?>
            </a>
        <?php endforeach; ?>

        <?php if (!empty($menu['role'])): ?>
            <div class="sidebar-section text-slate-500 text-[10px] uppercase tracking-widest px-3.5 pt-4 pb-2 font-bold">
                <?= htmlspecialchars(role_label($user['role'])) ?>
            </div>
            <?php foreach ($menu['role'] as $item):
                $isActive = ($active === $item['key']) ? ' is-active' : '';
            ?>
                <a href="<?= BASE_URL . $item['url'] ?>" class="nav-item<?= $isActive ?>">
                    <span class="nav-icon"><?= icon($item['icon'], 'w-5 h-5') ?></span>
                    <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="sidebar-foot mt-auto pt-4 border-t border-white/5">
            <div class="user-card flex items-center gap-2.5 p-2.5 rounded-xl bg-white/[0.03]">
                <div class="avatar w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-[13px] shrink-0"
                     style="background: linear-gradient(135deg,#1d4ed8,#0ea5e9);">
                    <?= htmlspecialchars($initials ?: '?') ?>
                </div>
                <div class="who flex-1 min-w-0">
                    <div class="text-slate-100 text-[13px] font-semibold truncate"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                    <div class="text-slate-500 text-[10.5px] uppercase tracking-wider font-bold mt-0.5"><?= htmlspecialchars(role_label($user['role'])) ?></div>
                </div>
                <a href="<?= $logout ?>" class="logout-btn text-slate-400 border border-white/10 rounded-lg p-2 inline-flex hover:bg-red-500/15 hover:text-red-300 hover:border-red-500/30 transition" title="Se déconnecter">
                    <?= icon('log-out', 'w-4 h-4') ?>
                </a>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="main flex flex-col min-w-0">
        <header class="topbar bg-white border-b border-slate-200 px-5 md:px-8 py-3.5 flex items-center justify-between gap-4 sticky top-0 z-20 min-h-[68px]">
            <div class="min-w-0">
                <div class="crumb flex items-center gap-2.5 text-slate-500 text-[11px] uppercase tracking-widest font-bold">
                    <button class="menu-toggle hidden bg-white border border-slate-200 rounded-lg p-1.5 text-ink-soft hover:bg-slate-100"
                            type="button"
                            onclick="document.getElementById('app').classList.toggle('is-open')"
                            aria-label="Menu">
                        <?= icon('menu', 'w-5 h-5') ?>
                    </button>
                    <span><?= htmlspecialchars($section) ?></span>
                </div>
                <h1 class="page-title text-[22px] text-ink font-bold tracking-tight mt-1 leading-tight"><?= htmlspecialchars($title) ?></h1>
            </div>
            <div class="actions flex gap-2 items-center shrink-0">
                <a href="<?= BASE_URL ?>/View/shared/notifications.php" class="btn btn-ghost btn-sm" title="Notifications">
                    <?= icon('bell', 'w-4 h-4') ?>
                    <?php if ($nbNotifs > 0): ?>
                        <span class="badge badge-info"><?= $nbNotifs ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <main class="content p-5 md:p-8 max-w-[1320px] w-full pb-16">
<?php
}

function layout_close(): void
{
    ?>
        </main>
    </div>
</div>
</body>
</html>
<?php
}
