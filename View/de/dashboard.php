<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/NotificationRepository.php';

$pageTitle = "Dashboard DE";
$de = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$nbEtudiants = count($de->listerEtudiants());
$nbProfs     = count($de->listerProfs());
$nbMemoires  = MemoireRepository::compterTotal();
$nbNotifs    = NotificationRepository::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Tableau de bord";
$pageSection = "Direction des études";
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

    <div class="banner" style="background:linear-gradient(135deg,#0f4c39,#1b998b)">
      <div class="banner-title">Tableau de bord — DE <?= icon('cap', 'w-4 h-4 inline-block align-text-bottom') ?></div>
      <div class="banner-sub">Direction des Études · UATM GASA Formation</div>
    </div>

    <div class="stats">
      <div class="stat"><div class="stat-n"><?= $nbEtudiants ?></div><div class="stat-l">Étudiants</div></div>
      <div class="stat"><div class="stat-n"><?= $nbProfs ?></div><div class="stat-l">Professeurs</div></div>
      <div class="stat"><div class="stat-n"><?= $nbMemoires ?></div><div class="stat-l">Mémoires</div></div>
      <div class="stat"><div class="stat-n" style="color:var(--warning)"><?= $nbNotifs ?></div><div class="stat-l">Notifications</div></div>
    </div>

    <div class="card">
      <div class="card-title">Tableau récapitulatif</div>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.78rem;">
          <thead>
            <tr style="background:var(--navy);color:#fff;">
              <th style="padding:.6rem;text-align:left;border-bottom:2px solid var(--navy);">Catégorie</th>
              <th style="padding:.6rem;text-align:center;border-bottom:2px solid var(--navy);">Total</th>
              <th style="padding:.6rem;text-align:center;border-bottom:2px solid var(--navy);">Détails</th>
            </tr>
          </thead>
          <tbody>
            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
              <td style="padding:.6rem;font-weight:600;"><i class="fas fa-users" style="color:var(--navy);margin-right:.4rem;"></i> Étudiants</td>
              <td style="padding:.6rem;text-align:center;font-weight:700;color:var(--navy);"><?= $nbEtudiants ?></td>
              <td style="padding:.6rem;text-align:center;">
                <a href="liste_etudiants.php" style="color:var(--emerald);font-weight:600;text-decoration:none;">Voir la liste →</a>
              </td>
            </tr>
            <tr style="background:#fff;border-bottom:1px solid #e5e7eb;">
              <td style="padding:.6rem;font-weight:600;"><i class="fas fa-chalkboard-teacher" style="color:var(--emerald);margin-right:.4rem;"></i> Professeurs</td>
              <td style="padding:.6rem;text-align:center;font-weight:700;color:var(--emerald);"><?= $nbProfs ?></td>
              <td style="padding:.6rem;text-align:center;">
                <a href="liste_profs.php" style="color:var(--emerald);font-weight:600;text-decoration:none;">Voir la liste →</a>
              </td>
            </tr>
            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
              <td style="padding:.6rem;font-weight:600;"><i class="fas fa-book" style="color:var(--gold);margin-right:.4rem;"></i> Mémoires</td>
              <td style="padding:.6rem;text-align:center;font-weight:700;color:var(--gold);"><?= $nbMemoires ?></td>
              <td style="padding:.6rem;text-align:center;">
                <a href="../shared/liste_memoires.php" style="color:var(--emerald);font-weight:600;text-decoration:none;">Consulter →</a>
              </td>
            </tr>
            <tr style="background:#fff;border-bottom:1px solid #e5e7eb;">
              <td style="padding:.6rem;font-weight:600;"><i class="fas fa-bell" style="color:var(--warning);margin-right:.4rem;"></i> Notifications</td>
              <td style="padding:.6rem;text-align:center;font-weight:700;color:var(--warning);"><?= $nbNotifs ?></td>
              <td style="padding:.6rem;text-align:center;">
                <a href="../shared/notifications.php" style="color:var(--emerald);font-weight:600;text-decoration:none;">Voir →</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e5e7eb;">
        <div style="font-size:.75rem;font-weight:600;color:var(--gray-700);margin-bottom:.5rem;">Actions rapides</div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
          <a href="activation_upload.php" class="btn btn-emerald btn-sm" style="flex:1;min-width:140px;justify-content:center;"> Activation en masse</a>
          <a href="upload_ancien.php" class="btn btn-emerald btn-sm" style="flex:1;min-width:140px;justify-content:center;"> Uploader ancien mémoire</a>
          <a href="valider_memoires.php" class="btn btn-success btn-sm" style="flex:1;min-width:140px;justify-content:center;"> Valider mémoires</a>
          <a href="gestion_visibilite.php" class="btn btn-warning btn-sm" style="flex:1;min-width:140px;justify-content:center;"> Gérer visibilité</a>
        </div>
      </div>
    </div>
<?php layout_close(); ?>