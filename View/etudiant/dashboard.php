<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle    = "Mon espace";
$nbNotifs     = Notification::compterNonLus($_SESSION['idCompte']);
$pdo          = getConnexion();

// Vérifier si l'utilisateur est auteur principal
$stmt = $pdo->prepare("SELECT COUNT(*) FROM memoire WHERE idCompte = ?");
$stmt->execute([$_SESSION['idCompte']]);
$estAuteurPrincipal = $stmt->fetchColumn() > 0;

$stmt = $pdo->prepare("SELECT peutUploader FROM etudiant WHERE idUtilisateur = ?");
$stmt->execute([$_SESSION['idUtilisateur']]);
$peutUploaderReel = (bool)$stmt->fetchColumn();

// Mes mémoires (auteur)
$stmt = $pdo->prepare("
    SELECT m.*, p.nom AS nomProf, p.prenom AS prenomProf 
    FROM memoire m 
    JOIN utilisateur p ON m.idProf = p.idUtilisateur 
    WHERE m.idCompte = ? 
    ORDER BY m.dateUpload DESC
");
$stmt->execute([$_SESSION['idCompte']]);
$mesMemoires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mémoires comme co-auteur accepté
$stmt = $pdo->prepare("
    SELECT m.*, p.nom AS nomProf, p.prenom AS prenomProf 
    FROM co_auteur ca 
    JOIN memoire m ON ca.idMemoire = m.idMemoire 
    JOIN utilisateur p ON m.idProf = p.idUtilisateur 
    WHERE ca.idCompte = ? AND ca.statut = 'ACCEPTE' 
    ORDER BY m.dateUpload DESC
");
$stmt->execute([$_SESSION['idCompte']]);
$memoiresCoAuth = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Invitations en attente (avec lien vers la page d'invitation)
$invitations = MemoireRepository::getInvitationsEnAttente($_SESSION['idCompte']);

// Info étudiant
$stmt = $pdo->prepare("SELECT filiere, option_etu, niveau FROM etudiant WHERE idUtilisateur = ?");
$stmt->execute([$_SESSION['idUtilisateur']]);
$inf = $stmt->fetch(PDO::FETCH_ASSOC);

function statusBadge(string $s): string {
    return match($s) {
        'EN_ATTENTE'           => '<span class="badge b-wait">⏳ En attente encadreur</span>',
        'EN_ATTENTE_DIRECTION' => '<span class="badge" style="background:#dbeafe;color:#1e40af;"> En attente Direction</span>',
        'VALIDE'               => '<span class="badge b-ok"> Publié</span>',
        'REJETE'               => '<span class="badge b-ko"> Rejeté</span>',
        'BROUILLON'            => '<span class="badge" style="background:#fef3c7;color:#92400e;"> Brouillon (en attente co-auteur)</span>',
        default                => '<span class="badge">'.$s.'</span>'
    };
}
?>
<?php
$pageTitle   = "Tableau de bord";
$pageSection = "Espace étudiant";
$activeRoute = "dashboard";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <div class="banner" style="background:linear-gradient(135deg,#061640,#0a2463);">
      <div class="banner-title">Bonjour, <?= htmlspecialchars($prenom) ?> </div>
      <div class="banner-sub">
        <?= htmlspecialchars($inf['filiere']??'') ?><?= !empty($inf['option_etu']) ? ' · '.htmlspecialchars($inf['option_etu']) : '' ?><?= !empty($inf['niveau']) ? ' · '.htmlspecialchars($inf['niveau']) : '' ?>
        &nbsp;·&nbsp;
        <?= $peutUploaderReel ? '<span style="color:#4ade80;font-weight:600;"> Dépôt autorisé</span>' : '<span style="color:#fbbf24;">Consultant</span>' ?>
      </div>
    </div>

    <!-- INVITATIONS EN ATTENTE (nouveau lien) -->
    <?php if (!empty($invitations)): ?>
    <div class="card" style="border:2px solid #f59e0b;background:#fffbeb;">
      <div class="card-title" style="color:#92400e;"> Invitations à être co-auteur (<?= count($invitations) ?>)</div>
      <?php foreach ($invitations as $inv): ?>
      <div style="background:#fff;border:1px solid #fde68a;border-radius:8px;padding:.8rem 1rem;margin-bottom:.6rem;">
        <div style="font-size:.82rem;font-weight:700;color:var(--gray-800);margin-bottom:.3rem;">
          "<?= htmlspecialchars($inv['theme']) ?>"
        </div>
        <div style="font-size:.72rem;color:var(--gray-500);margin-bottom:.6rem;">
          Invitation de <strong><?= htmlspecialchars($inv['prenomAuteur'].' '.$inv['nomAuteur']) ?></strong>
        </div>
        <div style="display:flex;gap:.5rem;">
          <a href="../../Controller/traitement_auth.php?action=gererInvitation&id=<?= $inv['idMemoire'] ?>" class="btn btn-primary btn-sm">
            <?= icon('edit', 'w-4 h-4 inline-block align-text-bottom') ?> Voir et modifier
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Bouton soumettre pour auteur principal -->
    <?php if ($estAuteurPrincipal && $peutUploaderReel): ?>
    <div class="card" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;">
        <div>
          <div style="font-size:.85rem;font-weight:700;color:#166534;"> Upload activé</div>
          <div style="font-size:.73rem;color:#166534;opacity:.8;">Vous pouvez soumettre votre mémoire.</div>
        </div>
        <a href="upload.php" class="btn btn-success"> Soumettre mon mémoire</a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Mes mémoires -->
    <?php if (!empty($mesMemoires) || !empty($memoiresCoAuth)): ?>
    <div class="card">
      <div class="card-title"> Mes mémoires</div>

      <?php foreach (array_merge($mesMemoires, $memoiresCoAuth) as $mem): ?>
      <?php
        $isCoAuth = !in_array($mem, $mesMemoires);
        $coAuteurs = MemoireRepository::getCoAuteurs($mem['idMemoire']);
        $coAcceptes = array_filter($coAuteurs, fn($c) => $c['statut'] === 'ACCEPTE');
      ?>
      <div style="border:1px solid var(--gray-200);border-radius:10px;padding:.9rem 1rem;margin-bottom:.7rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.3rem;margin-bottom:.4rem;">
          <div style="font-size:.85rem;font-weight:700;color:var(--gray-800);flex:1;"><?= htmlspecialchars($mem['theme']) ?></div>
          <?= statusBadge($mem['statut']) ?>
          <?php if ($isCoAuth): ?><span class="badge" style="background:#ede9fe;color:#6d28d9;">Co-auteur</span><?php endif; ?>
        </div>
        <div style="font-size:.72rem;color:var(--gray-500);margin-bottom:.3rem;">
          Prof. <?= htmlspecialchars($mem['nomProf'].' '.$mem['prenomProf']) ?> &nbsp;·&nbsp;
          <?= htmlspecialchars($mem['anneeAcademique']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($mem['centre']) ?>
        </div>
        <?php if (!empty($coAcceptes)): ?>
        <div style="font-size:.7rem;color:#6d28d9;margin-bottom:.3rem;">
          <?= icon('users', 'w-4 h-4 inline-block align-text-bottom') ?> Binôme avec :
          <?php foreach ($coAcceptes as $co): ?>
          <strong><?= htmlspecialchars($co['prenom'].' '.$co['nom']) ?></strong>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($mem['statut'] === 'REJETE' && !empty($mem['commentaireJury'])): ?>
        <div style="background:#fef2f2;border-left:3px solid #dc2626;padding:.4rem .7rem;border-radius:0 6px 6px 0;font-size:.73rem;color:#dc2626;margin:.4rem 0;">
          <?= icon('alert', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($mem['commentaireJury']) ?>
        </div>
        <?php endif; ?>
        <?php if ($mem['statut'] === 'EN_ATTENTE_DIRECTION'): ?>
        <div style="background:#eff6ff;border-left:3px solid #3b82f6;padding:.4rem .7rem;border-radius:0 6px 6px 0;font-size:.73rem;color:#1d4ed8;margin:.4rem 0;">
          <?= icon('sparkles', 'w-4 h-4 inline-block align-text-bottom') ?> Validé par votre encadreur. En attente de publication par la Direction.
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
          <?php if ($mem['statut'] === 'VALIDE'): ?>
          <a href="../shared/detail_memoire.php?id=<?= $mem['idMemoire'] ?>" class="btn btn-primary btn-sm"> Consulter</a>
          <?php endif; ?>
          <?php if ($mem['statut'] === 'REJETE' && $peutUploaderReel && !$isCoAuth): ?>
          <a href="upload.php" class="btn btn-warning btn-sm"> Soumettre une correction</a>
          <?php endif; ?>
          <a href="mon_memoire.php?id=<?= $mem['idMemoire'] ?>" class="btn btn-info btn-sm" style="background:#3b82f6;color:#fff;">
            <?= icon('sparkles', 'w-4 h-4 inline-block align-text-bottom') ?> Suivre l'actualité
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="stats">
      <div class="stat" onclick="location.href='../shared/liste_memoires.php'" style="cursor:pointer;">
        <div class="stat-n"><i class="fas fa-book" style="font-size:1.1rem;"></i></div>
        <div class="stat-l">Consulter les mémoires</div>
      </div>
      <div class="stat" onclick="location.href='../shared/notifications.php'" style="cursor:pointer;">
        <div class="stat-n" style="color:var(--warning);"><?= $nbNotifs ?></div>
        <div class="stat-l">Notifications</div>
      </div>
    </div>
<?php layout_close(); ?>