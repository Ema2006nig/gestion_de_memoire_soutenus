<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/LikeRepository.php';
require_once __DIR__ . '/../../Repository/CommentaireRepository.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$idMemoire = $_GET['id'] ?? '';
if (!$idMemoire) {
    header('Location: dashboard.php');
    exit;
}

$memoire = MemoireRepository::findById($idMemoire);
if (!$memoire) {
    echo "Mémoire introuvable.";
    exit;
}

$pdo = getConnexion();
$stmt = $pdo->prepare("
    SELECT 1 FROM memoire WHERE idMemoire = ? AND idCompte = ?
    UNION
    SELECT 1 FROM co_auteur WHERE idMemoire = ? AND idCompte = ? AND statut = 'ACCEPTE'
");
$stmt->execute([$idMemoire, $_SESSION['idCompte'], $idMemoire, $_SESSION['idCompte']]);
if (!$stmt->fetch()) {
    echo "Accès non autorisé à ce mémoire.";
    exit;
}

$likes = LikeRepository::findByMemoire($idMemoire);
$commentaires = CommentaireRepository::getByMemoire($idMemoire);
$nbNotifs = Notification::compterNonLus($_SESSION['idCompte']);
$pageTitle = "Actualités du mémoire : " . htmlspecialchars($memoire['theme']);
?>
<?php
$pageTitle   = "Suivi de mon mémoire";
$pageSection = "Espace étudiant";
$activeRoute = "dashboard";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<a href="dashboard.php" class="btn btn-ghost btn-sm" style="margin-bottom:1rem;"> Retour au dashboard</a>

    <div class="card">
      <div class="card-title"> Actualités du mémoire</div>
      <div style="margin-bottom:1rem;">
        <strong><?= htmlspecialchars($memoire['theme']) ?></strong><br>
        <span class="badge b-ok"><?= $memoire['statut'] ?></span>
      </div>

      <div style="margin-bottom:1.5rem;">
        <h3 style="font-size:.85rem;font-weight:700;margin-bottom:.5rem;"><i class="fas fa-heart" style="color:#ef4444;"></i> Likes reçus (<?= count($likes) ?>)</h3>
        <?php if (empty($likes)): ?>
        <div class="alert alert-info">Aucun like pour le moment.</div>
        <?php else: ?>
        <ul style="list-style:none;padding:0;">
          <?php foreach ($likes as $like): ?>
          <li style="padding:.4rem 0;border-bottom:1px solid var(--gray-200);">
            <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?> <strong><?= htmlspecialchars($like['prenom'] . ' ' . $like['nom']) ?></strong>
            a aimé le <?= date('d/m/Y à H:i', strtotime($like['dateLike'])) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>

      <div>
        <h3 style="font-size:.85rem;font-weight:700;margin-bottom:.5rem;"> Commentaires reçus (<?= count($commentaires) ?>)</h3>
        <?php if (empty($commentaires)): ?>
        <div class="alert alert-info">Aucun commentaire pour le moment.</div>
        <?php else: ?>
        <?php foreach ($commentaires as $c): ?>
        <div style="background:var(--gray-50);border-radius:8px;padding:.6rem .8rem;margin-bottom:.6rem;">
          <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.2rem;">
            <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
            <span style="font-size:.65rem;color:var(--gray-400);">· <?= date('d/m/Y H:i', strtotime($c['dateCommentaire'])) ?></span>
            <?php if ($c['note'] > 0): ?>
            <span class="badge" style="background:var(--gold);color:#000;">Note : <?= $c['note'] ?>/5</span>
            <?php endif; ?>
          </div>
          <div style="font-size:.8rem;"><?= nl2br(htmlspecialchars($c['contenu'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
<?php layout_close(); ?>