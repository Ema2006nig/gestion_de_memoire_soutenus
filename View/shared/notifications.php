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

<!-- NE PAS inclure header.php ici car layout_open() le fait déjà -->
<!-- <?php // include __DIR__ . '/header.php'; ?> -->

<div class="max-w-4xl mx-auto">
  <div class="flex flex-wrap justify-between items-center gap-3 mb-5">
    <div class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center">
        <i class="fas fa-bell text-emerald-600 text-sm"></i>
      </div>
      <h2 class="text-base font-bold text-gray-800">Notifications</h2>
      <?php if ($nbNotifs > 0): ?>
        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-600 text-white ml-1"><?= $nbNotifs ?> non lue(s)</span>
      <?php endif; ?>
    </div>
    <?php if ($nbNotifs > 0): ?>
      <a href="../../Controller/traitement_auth.php?action=tout_lu" 
         class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
        <i class="fas fa-check-double"></i> Tout marquer lu
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($notifs)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 text-center py-12">
      <div class="text-5xl mb-3 text-gray-300">
        <i class="fas fa-bell-slash"></i>
      </div>
      <div class="text-sm text-gray-500">Aucune notification pour le moment</div>
      <div class="text-xs text-gray-400 mt-1">Revenez plus tard pour voir les mises à jour</div>
    </div>
  <?php else: ?>
    <div class="space-y-2">
    <?php foreach ($notifs as $n):
      $unread  = !$n['lu'];
      $preview = mb_strlen($n['message']) > 80 ? mb_substr($n['message'], 0, 80) . '…' : $n['message'];
      
      $iconMap = [
        'VALIDATION' => 'fa-check-circle',
        'PUBLICATION' => 'fa-globe',
        'REJET' => 'fa-times-circle',
        'SOUMISSION' => 'fa-paper-plane',
        'LIKE' => 'fa-heart',
        'COMMENTAIRE' => 'fa-comment',
        'INVITATION' => 'fa-user-plus',
      ];
      $iconClass = $iconMap[$n['typeNotif']] ?? 'fa-bell';
      
      $bgMap = [
        'VALIDATION' => 'bg-green-100',
        'PUBLICATION' => 'bg-emerald-100',
        'REJET' => 'bg-red-100',
        'SOUMISSION' => 'bg-blue-100',
        'LIKE' => 'bg-pink-100',
        'COMMENTAIRE' => 'bg-purple-100',
        'INVITATION' => 'bg-yellow-100',
      ];
      $bgClass = $bgMap[$n['typeNotif']] ?? 'bg-gray-100';
      
      $iconColorMap = [
        'VALIDATION' => 'text-green-600',
        'PUBLICATION' => 'text-emerald-600',
        'REJET' => 'text-red-600',
        'SOUMISSION' => 'text-blue-600',
        'LIKE' => 'text-pink-600',
        'COMMENTAIRE' => 'text-purple-600',
        'INVITATION' => 'text-yellow-600',
      ];
      $iconColor = $iconColorMap[$n['typeNotif']] ?? 'text-gray-500';
    ?>
      <div class="bg-white rounded-xl shadow-sm border <?= $unread ? 'border-blue-300 bg-blue-50/30' : 'border-gray-200' ?> p-4 transition hover:shadow-md">
        <div class="flex gap-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full <?= $bgClass ?> flex items-center justify-center">
              <i class="fas <?= $iconClass ?> <?= $iconColor ?> text-base"></i>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <!-- Message -->
            <div class="text-sm <?= $unread ? 'font-semibold text-gray-800' : 'text-gray-600' ?> mb-1">
              <?= htmlspecialchars($preview) ?>
            </div>
            
            <!-- Message complet si tronqué -->
            <?php if (mb_strlen($n['message']) > 80): ?>
              <div id="full_<?= $n['idNotif'] ?>" class="hidden text-sm text-gray-600 mt-1">
                <?= htmlspecialchars($n['message']) ?>
              </div>
              <button onclick="toggleMsg('<?= $n['idNotif'] ?>')" 
                      class="text-xs text-emerald-600 hover:text-emerald-700 font-medium mt-1 focus:outline-none">
                <i class="fas fa-chevron-down text-xs" id="icon_<?= $n['idNotif'] ?>"></i>
                <span id="btn_<?= $n['idNotif'] ?>">Voir plus</span>
              </button>
            <?php endif; ?>
            
            <!-- Date -->
            <div class="flex items-center gap-2 mt-2 text-xs text-gray-400">
              <i class="far fa-clock"></i>
              <span><?= date('d/m/Y à H:i', strtotime($n['dateNotif'])) ?></span>
              <?php if ($unread): ?>
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-blue-600 font-medium">Non lue</span>
              <?php endif; ?>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-wrap gap-2 mt-3">
              <?php if ($n['idMemoire']): ?>
                <a href="../../Controller/traitement_auth.php?action=marquer_lu_et_rediriger&id=<?= $n['idNotif'] ?>&memoire=<?= urlencode($n['idMemoire']) ?>" 
                   class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                  <i class="fas fa-eye text-xs"></i> Voir le mémoire
                </a>
              <?php endif; ?>
              <?php if ($unread): ?>
                <a href="../../Controller/traitement_auth.php?action=marquer_lu&id=<?= $n['idNotif'] ?>" 
                   class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                  <i class="fas fa-check text-xs"></i> Marquer comme lue
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function toggleMsg(id) {
  const fullEl = document.getElementById('full_' + id);
  const btnEl = document.getElementById('btn_' + id);
  const iconEl = document.getElementById('icon_' + id);
  if (!fullEl) return;
  
  const isHidden = fullEl.classList.contains('hidden');
  if (isHidden) {
    fullEl.classList.remove('hidden');
    btnEl.textContent = 'Voir moins';
    if (iconEl) iconEl.className = 'fas fa-chevron-up text-xs';
  } else {
    fullEl.classList.add('hidden');
    btnEl.textContent = 'Voir plus';
    if (iconEl) iconEl.className = 'fas fa-chevron-down text-xs';
  }
}
</script>

<?php layout_close(); ?>