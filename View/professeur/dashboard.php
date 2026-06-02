<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('PROFESSEUR');
require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/NotificationRepository.php';

$pageTitle = "Dashboard Professeur";
$prof      = Professeur::trouverParId($_SESSION['idUtilisateur']);
$enAttente = $prof ? $prof->getMemoiresAValider() : [];
$nbNotifs  = NotificationRepository::compterNonLus($_SESSION['idCompte']);
$total     = MemoireRepository::compterTotal();
?>
<?php
$pageTitle   = "Tableau de bord";
$pageSection = "Espace enseignant";
$activeRoute = "dashboard";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>

    <div class="banner" style="background:linear-gradient(135deg,#3b0764,#7c3aed)">
      <div class="banner-title">Bonjour, Prof. <?= htmlspecialchars($_SESSION['nom']) ?> <?= icon('circle', 'w-4 h-4 inline-block align-text-bottom') ?></div>
      <div class="banner-sub">
        <?= htmlspecialchars($prof ? $prof->getSpecialite() : '') ?>
      </div>
    </div>

    <div class="stats">
      <div class="stat"><div class="stat-n"><?= $total ?></div><div class="stat-l">Total mémoires</div></div>
      <div class="stat" style="background:#fef3c7"><div class="stat-n" style="color:var(--warning)"><?= count($enAttente) ?></div><div class="stat-l">En attente</div></div>
      <div class="stat"><div class="stat-n" style="color:var(--navy)"><?= $nbNotifs ?></div><div class="stat-l">Notifications</div></div>
    </div>

    <?php if (!empty($enAttente)): ?>
    <div class="card">
      <div class="card-title">⏳ Mémoires à valider (<?= count($enAttente) ?>)</div>
      <?php foreach (array_slice($enAttente, 0, 3) as $m): ?>
      <div style="border:1.5px solid #fde68a;background:#fffbeb;border-radius:var(--radius-sm);padding:.8rem;margin-bottom:.6rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.4rem;">
          <div>
            <div style="font-size:.8rem;font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($m['theme']) ?></div>
            <div style="font-size:.68rem;color:var(--gray-500);margin-top:2px;">
              <?= htmlspecialchars($m['prenomEtudiant'] . ' ' . $m['nomEtudiant']) ?> · 
              <?= htmlspecialchars($m['anneeAcademique']) ?>
            </div>
          </div>
          <a href="../shared/detail_memoire.php?id=<?= $m['idMemoire'] ?>" class="btn btn-ghost btn-sm"> Voir</a>
        </div>
        <div style="display:flex;gap:.4rem;">
          <form method="POST" action="../../Controller/traitement_auth.php" style="flex:1">
            <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
            <button class="btn btn-success btn-sm btn-full" type="submit" name="btn_valider"> Valider</button>
          </form>
          <a href="validation.php?id=<?= $m['idMemoire'] ?>" class="btn btn-danger btn-sm" style="flex:1;justify-content:center;"> Rejeter</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (count($enAttente) > 3): ?>
      <a href="validation.php" class="btn btn-primary btn-sm btn-full"> Voir tous (<?= count($enAttente) ?>)</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-title">Actions rapides</div>
      <a href="../shared/liste_memoires.php" class="btn btn-primary btn-sm" style="margin-right:.5rem;"> Consulter les mémoires</a>
      <a href="validation.php" class="btn btn-emerald btn-sm"> Page de validation</a>
    </div>
<?php layout_close(); ?>