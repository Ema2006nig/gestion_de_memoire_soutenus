<?php
/**
 * Layout principal pour toutes les pages connectées (DE, Prof, Étudiant, Bibliothécaire).
 *
 * Utilisation :
 *
 *   <?php
 *   $pageTitle    = "Tableau de bord";
 *   $pageSection  = "Espace étudiant";          // facultatif
 *   $activeRoute  = "dashboard";                // identifiant utilisé par la sidebar
 *   include __DIR__ . '/../shared/layout.php';
 *   layout_open();
 *   ?>
 *   ... contenu HTML ...
 *   <?php layout_close(); ?>
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../Config/connexion.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/../../classes/Notification.php';

/* Variables exposées à toutes les vues (compatibilité ascendante). */
$idCompte      = $_SESSION['idCompte']      ?? '';
$idUtilisateur = $_SESSION['idUtilisateur'] ?? '';
$nom           = $_SESSION['nom']           ?? '';
$prenom        = $_SESSION['prenom']        ?? '';
$email         = $_SESSION['email']         ?? '';
$role          = $_SESSION['role']          ?? '';
$nbNotifs      = $idCompte ? Notification::compterNonLus($idCompte) : 0;

/* ----------------------------------------------------------------
 *  Construction de la navigation en fonction du rôle connecté.
 * ---------------------------------------------------------------- */
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

        // L'étudiant ne voit "Soumettre" que s'il y est autorisé en base.
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

    // Profil — commun à tous
    $profilUrl = $base . '/' . strtolower($role) . '/profil.php';
    $menu['role'][] = ['key'=>'profil', 'label'=>'Mon profil', 'icon'=>'user', 'url' => $profilUrl];

    return $menu;
}

/* ----------------------------------------------------------------
 *  Helpers globaux pour l'utilisateur connecté.
 * ---------------------------------------------------------------- */
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

/* ----------------------------------------------------------------
 *  Rendu — ouverture / fermeture du layout.
 * ---------------------------------------------------------------- */
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

    $logout = BASE_URL . '/Controller/traitement_auth.php?action=logout';

    ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> · <?= APP_NAME ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <script src="<?= ASSETS_URL ?>/js/app.js" defer></script>
</head>
<body>
<div class="app" id="app">

    <aside class="sidebar">
        <a href="<?= BASE_URL ?>/index.php" class="sidebar-brand">
            <img src="<?= BASE_URL ?>/icone.jpg" alt="UATM">
            <div>
                <div class="name"><?= APP_NAME ?></div>
                <div class="sub">UATM · Gasa</div>
            </div>
        </a>

        <div class="sidebar-section">Navigation</div>
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
            <div class="sidebar-section"><?= htmlspecialchars(role_label($user['role'])) ?></div>
            <?php foreach ($menu['role'] as $item):
                $isActive = ($active === $item['key']) ? ' is-active' : '';
            ?>
                <a href="<?= BASE_URL . $item['url'] ?>" class="nav-item<?= $isActive ?>">
                    <span class="nav-icon"><?= icon($item['icon'], 'w-5 h-5') ?></span>
                    <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="sidebar-foot">
            <div class="user-card">
                <div class="avatar"><?= htmlspecialchars($initials ?: '?') ?></div>
                <div class="who">
                    <div class="full"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                    <div class="role"><?= htmlspecialchars(role_label($user['role'])) ?></div>
                </div>
                <a href="<?= $logout ?>" class="logout-btn" title="Se déconnecter">
                    <?= icon('log-out', 'w-4 h-4') ?>
                </a>
            </div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div>
                <div class="crumb">
                    <button class="menu-toggle" type="button" onclick="document.getElementById('app').classList.toggle('is-open')" aria-label="Menu">
                        <?= icon('menu', 'w-5 h-5') ?>
                    </button>
                    <span><?= htmlspecialchars($section) ?></span>
                </div>
                <h1 class="page-title"><?= htmlspecialchars($title) ?></h1>
            </div>
            <div class="actions">
                <a href="<?= BASE_URL ?>/View/shared/notifications.php" class="btn btn-ghost btn-sm" title="Notifications">
                    <?= icon('bell', 'w-4 h-4') ?>
                    <?php if ($nbNotifs > 0): ?>
                        <span class="badge badge-info"><?= $nbNotifs ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <main class="content">
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