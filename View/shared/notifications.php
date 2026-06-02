<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle = "Notifications";
$pdo       = getConnexion();
$stmt      = $pdo->prepare("SELECT * FROM notification WHERE idCompte=? ORDER BY dateNotif DESC");
$stmt->execute([$_SESSION['idCompte']]);
$notifs    = $stmt->fetchAll(PDO::FETCH_ASSOC);
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Notifications";
$pageSection = "Plateforme";
$activeRoute = "notifications";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php include __DIR__ . '/header.php'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h2 style="font-size:.9rem;font-weight:700;color:var(--navy);"> Notifications</h2>
      <?php if ($nbNotifs>0): ?>
      <a href="../../Controller/traitement_auth.php?action=tout_lu" class="btn btn-ghost btn-sm">
        <?= icon('check-double', 'w-4 h-4 inline-block align-text-bottom') ?> Tout marquer lu
      </a>
      <?php endif; ?>
    </div>

    <?php if (empty($notifs)): ?>
    <div class="card" style="text-align:center;padding:2.5rem;">
      <div style="font-size:2rem;margin-bottom:.5rem;"></div>
      <div style="font-size:.82rem;color:var(--gray-500);">Aucune notification.</div>
    </div>
    <?php else: ?>
    <?php foreach ($notifs as $n):
      $unread  = !$n['lu'];
      $preview = mb_strlen($n['message'])>80 ? mb_substr($n['message'],0,80).'…' : $n['message'];
      $icon    = match($n['typeNotif']) {
        'VALIDATION','PUBLICATION' => '', 'REJET' => '',
        'SOUMISSION' => '', 'LIKE' => '',
        'COMMENTAIRE' => '', 'INVITATION' => '',
        default => ''
      };
      $bg = match($n['typeNotif']) {
        'VALIDATION','PUBLICATION' => '#dcfce7', 'REJET' => '#fee2e2',
        'SOUMISSION' => '#dbeafe', 'LIKE' => '#fff0f0',
        'COMMENTAIRE' => '#ede9fe', 'INVITATION' => '#fef9c3',
        default => '#f3f4f6'
      };
    ?>
    <div style="display:flex;gap:.8rem;padding:.9rem 1rem;border-radius:10px;margin-bottom:.4rem;background:<?= $unread?'#eff6ff':'#fff' ?>;border:1px solid <?= $unread?'#bfdbfe':'var(--gray-200)' ?>;">
      <div style="width:36px;height:36px;border-radius:50%;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;"><?= $icon ?></div>
      <div style="flex:1;min-width:0;">
        <!-- Aperçu tronqué -->
        <div style="font-size:.8rem;color:var(--gray-<?= $unread?'800':'600' ?>);font-weight:<?= $unread?'600':'400' ?>;"><?= htmlspecialchars($preview) ?></div>
        <!-- Message complet si long -->
        <?php if (mb_strlen($n['message'])>80): ?>
        <div id="full_<?= $n['idNotif'] ?>" style="display:none;font-size:.8rem;color:var(--gray-700);margin-top:.2rem;font-weight:<?= $unread?'600':'400' ?>;">
          <?= htmlspecialchars($n['message']) ?>
        </div>
        <button onclick="toggleMsg('<?= $n['idNotif'] ?>')" style="font-size:.65rem;color:var(--navy);background:none;border:none;cursor:pointer;padding:0;margin-top:.2rem;" id="btn_<?= $n['idNotif'] ?>">
          Voir plus ▾
        </button>
        <?php endif; ?>
        <div style="font-size:.65rem;color:var(--gray-400);margin-top:.2rem;">
          <?= date('d/m/Y à H:i',strtotime($n['dateNotif'])) ?>
          <?php if ($unread): ?><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#3b82f6;margin-left:.4rem;vertical-align:middle;"></span><?php endif; ?>
        </div>
        <div style="display:flex;gap:.4rem;margin-top:.4rem;flex-wrap:wrap;">
          <?php if ($n['idMemoire']): ?>
          <a href="../../Controller/traitement_auth.php?action=marquer_lu_et_rediriger&id=<?= $n['idNotif'] ?>&memoire=<?= urlencode($n['idMemoire']) ?>" class="btn btn-primary btn-sm">
            <?= icon('eye', 'w-4 h-4 inline-block align-text-bottom') ?> Voir
          </a>
          <?php endif; ?>
          <?php if ($unread): ?>
          <a href="../../Controller/traitement_auth.php?action=marquer_lu&id=<?= $n['idNotif'] ?>" class="btn btn-ghost btn-sm">
            <?= icon('check', 'w-4 h-4 inline-block align-text-bottom') ?> Lu
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<script>
function toggleMsg(id) {
  const el  = document.getElementById('full_'+id);
  const btn = document.getElementById('btn_'+id);
  if (!el) return;
  const open = el.style.display==='none';
  el.style.display  = open ? 'block' : 'none';
  btn.textContent   = open ? 'Voir moins ▴' : 'Voir plus ▾';
}
</script>
<?php layout_close(); ?>