<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('BIBLIOTHECAIRE');
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle        = "Dashboard Bibliothécaire";
$nbNotifs         = Notification::compterNonLus($_SESSION['idCompte']);
$delegationActive = MemoireRepository::getDelegationActive();
$memoiresEnAtt    = [];

if ($delegationActive && $delegationActive['idBibliothecaire'] === $_SESSION['idUtilisateur']) {
    $memoiresEnAtt = MemoireRepository::findEnAttenteDirection();
}
?>
<?php
$pageTitle   = "Tableau de bord";
$pageSection = "Bibliothèque";
$activeRoute = "dashboard";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>

    <div class="banner" style="background:linear-gradient(135deg,#78350f,#d97706);">
      <div class="banner-title">Tableau de bord — Bibliothécaire <?= icon('library', 'w-4 h-4 inline-block align-text-bottom') ?></div>
      <div class="banner-sub">UATM GASA Formation · Gestion documentaire</div>
    </div>

    <div class="stats">
      <div class="stat"><div class="stat-n"><?= count($memoiresEnAtt) ?></div><div class="stat-l">À publier</div></div>
      <div class="stat"><div class="stat-n" style="color:var(--warning);"><?= $nbNotifs ?></div><div class="stat-l">Notifications</div></div>
    </div>

    <?php if (!$delegationActive || $delegationActive['idBibliothecaire'] !== $_SESSION['idUtilisateur']): ?>
    <div class="card" style="text-align:center;padding:2rem;">
      <div style="font-size:2rem;margin-bottom:.5rem;"></div>
      <div style="font-size:.85rem;font-weight:600;color:var(--gray-600);">Aucune délégation active</div>
      <div style="font-size:.75rem;color:var(--gray-400);margin-top:.3rem;">Le Directeur des Études doit vous déléguer la publication depuis son tableau de bord.</div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning" style="font-size:.78rem;">
      <?= icon('info', 'w-4 h-4 inline-block align-text-bottom') ?> La délégation est active. Vous pouvez publier les mémoires validés par les encadreurs.
    </div>
    <?php if (empty($memoiresEnAtt)): ?>
    <div class="card" style="text-align:center;padding:2rem;">
      <div style="font-size:2rem;margin-bottom:.5rem;"></div>
      <div style="font-size:.82rem;color:var(--gray-500);">Aucun mémoire en attente de publication.</div>
    </div>
    <?php else: ?>
    <?php foreach ($memoiresEnAtt as $m):
      $coAuteurs  = MemoireRepository::getCoAuteurs($m['idMemoire']);
      $coAcceptes = array_filter($coAuteurs, fn($c) => $c['statut']==='ACCEPTE');
    ?>
    <div style="border:1px solid #fcd34d;background:#fffbeb;border-radius:10px;padding:1rem;margin-bottom:.7rem;">
      <div style="font-size:.85rem;font-weight:700;color:var(--gray-800);margin-bottom:.3rem;"><?= htmlspecialchars($m['theme']) ?></div>
      <div style="font-size:.7rem;color:var(--gray-500);margin-bottom:.5rem;">
        <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($m['prenomEtudiant'].' '.$m['nomEtudiant']) ?>
        <?php foreach ($coAcceptes as $co): ?> &amp; <strong><?= htmlspecialchars($co['prenom'].' '.$co['nom']) ?></strong><?php endforeach; ?>
        &nbsp;·&nbsp; Prof. <?= htmlspecialchars($m['nomProf'].' '.$m['prenomProf']) ?>
        &nbsp;·&nbsp; <?= htmlspecialchars($m['anneeAcademique']) ?>
        <?php if (!empty($m['filiere'])): ?>&nbsp;·&nbsp; <?= htmlspecialchars($m['filiere']) ?><?php endif; ?>
      </div>
      <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
        <?php if ($m['fichierPdf']): ?>
        <a href="../../uploads/memoires/<?= htmlspecialchars($m['fichierPdf']) ?>" target="_blank" class="btn btn-primary btn-sm"> PDF</a>
        <?php endif; ?>
        <a href="../shared/detail_memoire.php?id=<?= $m['idMemoire'] ?>" class="btn btn-ghost btn-sm"> Consulter</a>
        <form method="POST" action="../../Controller/traitement_auth.php" style="display:inline;">
          <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
          <button class="btn btn-success btn-sm" type="submit" name="btn_publier" onclick="return confirm('Publier ce mémoire ?')">
            <?= icon('circle', 'w-4 h-4 inline-block align-text-bottom') ?> Publier
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>
<?php layout_close(); ?>