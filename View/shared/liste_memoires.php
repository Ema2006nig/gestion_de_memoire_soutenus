<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/NotificationRepository.php';

$pageTitle = "Recherche de mémoires";
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
$page      = $_GET['page'] ?? 1;
$perPage   = 20;

$memoires  = MemoireRepository::findAll(
    $_GET['filiere'] ?? '',
    $_GET['annee']   ?? '',
    $_GET['centre']  ?? '',
    $_GET['q']       ?? '',
    $_GET['type']    ?? ''
);
$filieres = MemoireRepository::getFilieres();
$annees   = MemoireRepository::getAnnees();
$centres  = MemoireRepository::getCentres();

$totalMemoires = count($memoires);
$totalPages    = ceil($totalMemoires / $perPage);
$memoires      = array_slice($memoires, ($page - 1) * $perPage, $perPage);

// Sidebar selon le rôle
$role = $_SESSION['role'];
$dashUrl = match($role) {
    'DE'         => '../de/dashboard.php',
    'PROFESSEUR' => '../professeur/dashboard.php',
    default      => '../etudiant/dashboard.php'
};
?>
<?php
$pageTitle   = "Bibliothèque des mémoires";
$pageSection = "Plateforme";
$activeRoute = "memoires";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<!-- Barre de recherche -->
    <div class="card" style="margin-bottom:1rem;">
      <form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
          <label class="fl">Recherche</label>
          <input class="fi" type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder=" Thème, auteur, mots-clés...">
        </div>
        <div style="min-width:140px;">
          <label class="fl">Filière</label>
          <select class="fsel" name="filiere">
            <option value="">Toutes</option>
            <?php foreach ($filieres as $f): ?>
            <option value="<?= htmlspecialchars($f) ?>" <?= ($_GET['filiere'] ?? '') === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="min-width:120px;">
          <label class="fl">Année</label>
          <select class="fsel" name="annee">
            <option value="">Toutes</option>
            <?php foreach ($annees as $a): ?>
            <option value="<?= htmlspecialchars($a) ?>" <?= ($_GET['annee'] ?? '') === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="min-width:130px;">
          <label class="fl">Centre</label>
          <select class="fsel" name="centre">
            <option value="">Tous</option>
            <?php foreach ($centres as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= ($_GET['centre'] ?? '') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="min-width:110px;">
          <label class="fl">Type</label>
          <select class="fsel" name="type">
            <option value="">Tous</option>
            <option value="ANCIEN" <?= ($_GET['type'] ?? '') === 'ANCIEN' ? 'selected' : '' ?>>Anciens</option>
            <option value="NOUVEAU" <?= ($_GET['type'] ?? '') === 'NOUVEAU' ? 'selected' : '' ?>>Nouveaux</option>
          </select>
        </div>
        <button class="btn btn-primary" type="submit">Chercher</button>
        <a href="liste_memoires.php" class="btn btn-ghost">Réinitialiser</a>
      </form>
    </div>

    <!-- Résultats -->
    <div style="font-size:.75rem;color:var(--gray-500);margin-bottom:.7rem;font-weight:500;">
      <?= $totalMemoires ?> mémoire(s) trouvé(s)
    </div>

    <?php if (empty($memoires)): ?>
    <div class="card" style="text-align:center;padding:2rem;">
      <div style="font-size:2rem;margin-bottom:.5rem;"></div>
      <div style="font-size:.82rem;color:var(--gray-500);">Aucun mémoire trouvé pour ces critères.</div>
    </div>
    <?php else: ?>
    <?php foreach ($memoires as $m): ?>
    <a href="detail_memoire.php?id=<?= $m['idMemoire'] ?>" style="text-decoration:none;color:inherit;">
      <div class="mcard">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
          <div class="mcard-title"><?= htmlspecialchars($m['theme']) ?></div>
          <span class="badge <?= $m['type'] === 'ANCIEN' ? 'b-anc' : 'b-ok' ?>" style="flex-shrink:0">
            <?= $m['type'] === 'ANCIEN' ? ' Ancien' : ' Nouveau' ?>
          </span>
        </div>
        <div class="mcard-meta">
          <span class="mtag"> <?= htmlspecialchars($m['filiere'] ?? '') ?></span>
          <span class="mtag"> <?= htmlspecialchars($m['anneeAcademique']) ?></span>
          <span class="mtag"> <?= htmlspecialchars($m['centre']) ?></span>
        </div>
        <div class="mcard-foot">
          <span> <?= htmlspecialchars($m['prenomEtudiant'] . ' ' . $m['nomEtudiant']) ?> ·  Prof. <?= htmlspecialchars($m['nomProf']) ?></span>
          <div class="mactions">
            <span class="like-btn"> <?= $m['nbLikes'] ?></span>
          </div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;align-items:center;gap:.5rem;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--gray-200);">
      <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>&filiere=<?= htmlspecialchars($_GET['filiere'] ?? '') ?>&annee=<?= htmlspecialchars($_GET['annee'] ?? '') ?>&centre=<?= htmlspecialchars($_GET['centre'] ?? '') ?>&q=<?= htmlspecialchars($_GET['q'] ?? '') ?>&type=<?= htmlspecialchars($_GET['type'] ?? '') ?>" class="btn btn-ghost btn-sm"> Précédent</a>
      <?php endif; ?>
      <span style="font-size:.75rem;color:var(--gray-600);">Page <?= $page ?> sur <?= $totalPages ?> (<?= $totalMemoires ?> mémoires)</span>
      <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>&filiere=<?= htmlspecialchars($_GET['filiere'] ?? '') ?>&annee=<?= htmlspecialchars($_GET['annee'] ?? '') ?>&centre=<?= htmlspecialchars($_GET['centre'] ?? '') ?>&q=<?= htmlspecialchars($_GET['q'] ?? '') ?>&type=<?= htmlspecialchars($_GET['type'] ?? '') ?>" class="btn btn-ghost btn-sm">Suivant <?= icon('arrow-right', 'w-4 h-4 inline-block align-text-bottom') ?></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
<?php layout_close(); ?>