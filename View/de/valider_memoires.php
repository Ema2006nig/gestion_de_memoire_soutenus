<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle       = "Validation des mémoires";
$nbNotifs        = Notification::compterNonLus($_SESSION['idCompte']);
$memoiresEnAtt   = MemoireRepository::findEnAttenteDirection();
$delegationActive= MemoireRepository::getDelegationActive();
$pdo             = getConnexion();

// Liste bibliothécaires
$stmt=$pdo->query("SELECT u.idUtilisateur,u.nom,u.prenom FROM bibliothecaire b JOIN utilisateur u ON b.idUtilisateur=u.idUtilisateur");
$bibliothecaires=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
$pageTitle   = "Validation finale";
$pageSection = "Direction des études";
$activeRoute = "valider";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <!-- BLOC DÉLÉGATION GLOBALE -->
    <?php if (!empty($bibliothecaires)): ?>
    <div class="card" style="border:2px solid <?= $delegationActive ? '#f59e0b' : 'var(--gray-200)' ?>;background:<?= $delegationActive ? '#fffbeb' : '#fff' ?>;">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;">
        <div>
          <div style="font-size:.82rem;font-weight:700;color:<?= $delegationActive ? '#92400e' : 'var(--navy)' ?>;">
            <?= icon('circle', 'w-4 h-4 inline-block align-text-bottom') ?> Délégation de publication au bibliothécaire
          </div>
          <?php if ($delegationActive): ?>
          <div style="font-size:.73rem;color:#92400e;margin-top:.2rem;">
             Active — <strong><?= htmlspecialchars($delegationActive['prenom'].' '.$delegationActive['nom']) ?></strong> peut publier tous les mémoires
          </div>
          <?php else: ?>
          <div style="font-size:.73rem;color:var(--gray-500);margin-top:.2rem;">
             Inactif — Vous seul publiez les mémoires
          </div>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
          <?php if ($delegationActive): ?>
          <form method="POST" action="../../Controller/traitement_auth.php">
            <button class="btn btn-danger btn-sm" type="submit" name="btn_desactiver_delegation">
              <?= icon('x', 'w-4 h-4 inline-block align-text-bottom') ?> Désactiver la délégation
            </button>
          </form>
          <?php else: ?>
          <form method="POST" action="../../Controller/traitement_auth.php" style="display:flex;gap:.4rem;align-items:center;">
            <select class="fsel" name="idBibliothecaire" required style="font-size:.75rem;height:auto;padding:.35rem .6rem;">
              <option value="">-- Bibliothécaire --</option>
              <?php foreach ($bibliothecaires as $b): ?>
              <option value="<?= htmlspecialchars($b['idUtilisateur']) ?>">
                <?= htmlspecialchars($b['prenom'].' '.$b['nom']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-warning btn-sm" type="submit" name="btn_activer_delegation">
              <?= icon('circle', 'w-4 h-4 inline-block align-text-bottom') ?> Déléguer
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- MÉMOIRES À PUBLIER -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
      <h2 style="font-size:.9rem;font-weight:700;color:var(--navy);">
        <?= icon('check-double', 'w-4 h-4 inline-block align-text-bottom') ?> Mémoires en attente de publication
      </h2>
      <span class="badge b-wait"><?= count($memoiresEnAtt) ?> en attente</span>
    </div>

    <?php if (empty($memoiresEnAtt)): ?>
    <div class="card" style="text-align:center;padding:2.5rem;">
      <div style="font-size:2rem;margin-bottom:.5rem;"></div>
      <div style="font-size:.85rem;color:var(--gray-500);">Aucun mémoire en attente de publication.</div>
    </div>
    <?php else: ?>
    <?php foreach ($memoiresEnAtt as $m):
      $coAuteurs = MemoireRepository::getCoAuteurs($m['idMemoire']);
      $coAcceptes = array_filter($coAuteurs, fn($c) => $c['statut']==='ACCEPTE');
    ?>
    <div style="border:1px solid #a3e635;background:#f7ffe4;border-radius:10px;padding:1rem;margin-bottom:.7rem;box-shadow:0 2px 8px rgba(0,0,0,.04);">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
        <div style="flex:1;">
          <div style="font-size:.85rem;font-weight:700;color:var(--gray-800);margin-bottom:.2rem;"><?= htmlspecialchars($m['theme']) ?></div>
          <div style="font-size:.7rem;color:var(--gray-500);">
            <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($m['prenomEtudiant'].' '.$m['nomEtudiant']) ?>
            <?php if (!empty($coAcceptes)): ?>
            &amp; <?php foreach ($coAcceptes as $co): ?><strong><?= htmlspecialchars($co['prenom'].' '.$co['nom']) ?></strong><?php endforeach; ?>
            <?php endif; ?>
            &nbsp;·&nbsp; <?= icon('cap', 'w-4 h-4 inline-block align-text-bottom') ?> Prof. <?= htmlspecialchars($m['nomProf'].' '.$m['prenomProf']) ?>
            &nbsp;·&nbsp; <?= icon('calendar', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($m['anneeAcademique']) ?>
            <?php if (!empty($m['filiere'])): ?>&nbsp;·&nbsp; <?= htmlspecialchars($m['filiere']) ?><?php endif; ?>
          </div>
        </div>
        <div style="display:flex;gap:.4rem;">
          <?php if ($m['fichierPdf']): ?>
          <a href="../../uploads/memoires/<?= htmlspecialchars($m['fichierPdf']) ?>" target="_blank" class="btn btn-primary btn-sm"> PDF</a>
          <?php endif; ?>
          <a href="../shared/detail_memoire.php?id=<?= $m['idMemoire'] ?>" class="btn btn-ghost btn-sm"></a>
        </div>
      </div>
      <form method="POST" action="../../Controller/traitement_auth.php">
        <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
        <button class="btn btn-success btn-sm" type="submit" name="btn_publier" onclick="return confirm('Publier ce mémoire ?')">
          <?= icon('circle', 'w-4 h-4 inline-block align-text-bottom') ?> Publier maintenant
        </button>
      </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
<?php layout_close(); ?>