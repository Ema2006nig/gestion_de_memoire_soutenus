<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Commentaire.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/CommentaireRepository.php';
require_once __DIR__ . '/../../Repository/LikeRepository.php';
require_once __DIR__ . '/../../Repository/NotificationRepository.php';

$id = $_GET['id'] ?? '';
$memoire = MemoireRepository::findById($id);
if (!$memoire) { echo "Mémoire introuvable."; exit; }

$commentaires = CommentaireRepository::getByMemoire($id);
$aLike = LikeRepository::aLike($_SESSION['idCompte'], $id);
$pageTitle = htmlspecialchars($memoire['theme']);
$nbNotifs = NotificationRepository::compterNonLus($_SESSION['idCompte']);
$role = $_SESSION['role'];
$dashUrl = match($role) {
    'DE' => '../de/dashboard.php',
    'PROFESSEUR' => '../professeur/dashboard.php',
    'BIBLIOTHECAIRE' => '../bibliothecaire/dashboard.php',
    default => '../etudiant/dashboard.php'
};
?>
<?php
$pageTitle   = "Détail du mémoire";
$pageSection = "Plateforme";
$activeRoute = "memoires";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php include __DIR__ . '/header.php'; ?>
<a href="liste_memoires.php" style="font-size:.75rem;color:var(--navy);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.8rem;">← Retour à la liste</a>

    <!-- En-tête mémoire -->
    <div class="memoire-content" style="background:linear-gradient(135deg,#061640,#0a2463,#1a3a7a);border-radius:var(--radius);padding:1.2rem 1.4rem;color:#fff;margin-bottom:1rem;position:relative;overflow:hidden;">
      <div style="position:absolute;right:-20px;top:-20px;width:120px;height:120px;background:rgba(255,255,255,.05);border-radius:50%;"></div>
      <div style="font-size:.65rem;color:var(--gold);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;">
        <?= $memoire['type'] === 'ANCIEN' ? ' Mémoire archivé' : ' Mémoire récent' ?>
      </div>
      <div style="font-size:1rem;font-weight:700;margin-bottom:.6rem;line-height:1.4;"><?= htmlspecialchars($memoire['theme']) ?></div>
      <div style="font-size:.72rem;opacity:.85;line-height:2;">
        <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($memoire['prenomEtudiant'] . ' ' . $memoire['nomEtudiant']) ?> &nbsp;|&nbsp;
        <?= icon('library', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($memoire['filiere'] ?? '') ?> · <?= htmlspecialchars($memoire['niveau'] ?? '') ?><br>
        <?= icon('calendar', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($memoire['anneeAcademique']) ?> &nbsp;|&nbsp;
        <?= icon('map-pin', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($memoire['centre']) ?> &nbsp;|&nbsp;
        <?= icon('cap', 'w-4 h-4 inline-block align-text-bottom') ?> Prof. <?= htmlspecialchars($memoire['nomProf'] . ' ' . $memoire['prenomProf']) ?>
      </div>
      <div style="display:flex;gap:.5rem;margin-top:.8rem;align-items:center;flex-wrap:wrap;">
        <?php if (!empty($memoire['fichierPdf'])): ?>
        <button type="button" onclick="window.open('../../uploads/memoires/<?= htmlspecialchars($memoire['fichierPdf']) ?>', '_blank')" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:6px 14px;font-size:.75rem;cursor:pointer;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;">
          <?= icon('file-text', 'w-4 h-4 inline-block align-text-bottom') ?> Voir le PDF
        </button>
        <?php else: ?>
        <span style="background:rgba(239,68,68,.2);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:6px 14px;font-size:.75rem;display:inline-flex;align-items:center;gap:.4rem;">
          <?= icon('alert', 'w-4 h-4 inline-block align-text-bottom') ?> Aucun fichier PDF
        </span>
        <?php endif; ?>
        <!-- Like style Facebook -->
        <form method="POST" action="../../Controller/traitement_auth.php" style="display:inline;">
          <input type="hidden" name="idMemoire" value="<?= $memoire['idMemoire'] ?>">
          <input type="hidden" name="action_like" value="1">
          <button type="submit" style="background:<?= $aLike ? 'rgba(239,68,68,.25)' : 'rgba(255,255,255,.15)' ?>;color:<?= $aLike ? '#fff' : 'rgba(255,255,255,.9)' ?>;border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:6px 14px;font-size:.75rem;cursor:pointer;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;">
            <i class="fas fa-heart" style="<?= $aLike ? 'color:#ef4444' : '' ?>"></i> <?= $memoire['nbLikes'] ?> J'aime
          </button>
        </form>
        <span style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:6px 14px;font-size:.75rem;display:inline-flex;align-items:center;gap:.4rem;">
          <?= icon('message', 'w-4 h-4 inline-block align-text-bottom') ?> <?= count($commentaires) ?> commentaires
        </span>
        <?php
        $badgeStyle = match($memoire['statut']) {
            'VALIDE'     => 'background:#d1fae5;color:#065f46;',
            'EN_ATTENTE' => 'background:#fef3c7;color:#92400e;',
            'REJETE'     => 'background:#fee2e2;color:#991b1b;',
            default      => ''
        };
        ?>
        <span style="<?= $badgeStyle ?> border-radius:20px;padding:3px 10px;font-size:.65rem;font-weight:700;margin-left:auto;">
          <?= $memoire['statut'] ?>
        </span>
      </div>
    </div>

    <!-- Commentaires -->
    <div class="card">
      <div style="font-size:.85rem;font-weight:700;color:var(--navy);margin-bottom:1rem;">
        <?= icon('message', 'w-4 h-4 inline-block align-text-bottom') ?> Commentaires (<?= count($commentaires) ?>)
      </div>

      <!-- Formulaire commentaire COMPLET avec tous les champs dans le form -->
      <form method="POST" action="../../Controller/traitement_auth.php" style="margin-bottom:1.5rem;">
        <input type="hidden" name="idMemoire" value="<?= $memoire['idMemoire'] ?>">
        <div style="display:flex;gap:.8rem;align-items:flex-start;">
          <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--navy),#1a3a7a);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">
            <?= strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1)) ?>
          </div>
          <div style="flex:1;">
            <textarea class="fi" name="contenu" rows="2" placeholder="Écrivez un commentaire..." style="resize:none;border-radius:12px;padding:.6rem 1rem;font-size:.82rem;" required></textarea>
            <?php if ($_SESSION['role'] === 'PROFESSEUR'): ?>
            <div style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;font-size:.75rem;color:var(--gray-500);">
              <i class="fas fa-star" style="color:var(--warning);"></i> Note (optionnel) :
              <select name="note" style="border:1px solid var(--gray-200);border-radius:6px;padding:2px 8px;font-size:.75rem;">
                <option value="0">—</option>
                <option value="1">1/5</option><option value="2">2/5</option>
                <option value="3">3/5</option><option value="4">4/5</option><option value="5">5/5</option>
              </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="note" value="0">
            <?php endif; ?>
            <button class="btn btn-primary btn-sm" type="submit" name="btn_commenter" style="margin-top:.5rem;border-radius:20px;">
              <?= icon('send', 'w-4 h-4 inline-block align-text-bottom') ?> Publier
            </button>
          </div>
        </div>
      </form>

      <div style="font-size:.85rem;font-weight:600;color:var(--gray-700);margin-bottom:1rem;">
        <?= icon('message', 'w-4 h-4 inline-block align-text-bottom') ?> Commentaires (<?= count($commentaires) ?>)
      </div>

      <?php foreach ($commentaires as $c): ?>
      <div style="display:flex;gap:.8rem;margin-bottom:1.2rem;">
        <div style="width:40px;height:40px;border-radius:50%;background:<?= $c['role'] === 'PROFESSEUR' ? 'linear-gradient(135deg,#7c3aed,#8b5cf6)' : 'linear-gradient(135deg,#1b998b,#2dc4b2)' ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;flex-shrink:0;">
          <?= strtoupper(substr($c['prenom'], 0, 1) . substr($c['nom'], 0, 1)) ?>
        </div>
        <div style="flex:1;">
          <div style="background:var(--gray-50);border-radius:12px;padding:.8rem 1rem;">
            <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.3rem;">
              <span style="font-size:.8rem;font-weight:700;color:var(--gray-800);"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></span>
              <span style="font-size:.65rem;color:var(--gray-400);">· <?= $c['dateCommentaire'] ?></span>
              <?php if ($c['note'] > 0): ?>
              <span style="margin-left:auto;font-size:.7rem;color:var(--warning);font-weight:600;"> <?= $c['note'] ?>/5</span>
              <?php endif; ?>
            </div>
            <div style="font-size:.85rem;color:var(--gray-700);line-height:1.5;"><?= htmlspecialchars($c['contenu']) ?></div>
          </div>
          <div style="display:flex;gap:1rem;margin-top:.4rem;margin-left:.5rem;">
            <button style="background:none;border:none;color:var(--gray-500);font-size:.75rem;font-weight:600;cursor:pointer;padding:0;">J'aime</button>
            <button style="background:none;border:none;color:var(--gray-500);font-size:.75rem;font-weight:600;cursor:pointer;padding:0;">Répondre</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
<?php layout_close(); ?>