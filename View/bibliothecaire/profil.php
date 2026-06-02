<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('BIBLIOTHECAIRE');
require_once __DIR__ . '/../../classes/Notification.php';
$pageTitle = "Mon profil";
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Mon profil";
$pageSection = "Bibliothèque";
$activeRoute = "profil";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<a href="dashboard.php" style="font-size:.75rem;color:var(--navy);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.8rem;">← Tableau de bord</a>
    <div class="card" style="max-width:480px;margin:0 auto;">
      <div class="card-title"> Mon profil</div>
      <div style="text-align:center;padding:1rem 0;">
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#78350f,#d97706);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;margin:0 auto .6rem;">
          <?= strtoupper(substr($prenom,0,1).substr($nom,0,1)) ?>
        </div>
        <div style="font-size:.95rem;font-weight:700;"><?= htmlspecialchars($prenom.' '.$nom) ?></div>
        <span class="badge" style="background:#78350f;color:#fff;margin-top:.4rem;">Bibliothécaire</span>
      </div>
      <?php if (isset($_SESSION['succes'])): ?><div class="alert alert-success"><?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div><?php endif; ?>
      <?php if (isset($_SESSION['erreur'])): ?><div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div><?php endif; ?>
      <div style="border-top:1px solid var(--gray-200);padding-top:1rem;margin-top:.5rem;">
        <div style="font-size:.8rem;font-weight:600;margin-bottom:.6rem;">Changer le mot de passe</div>
        <form method="POST" action="../../Controller/traitement_auth.php">
          <div class="fg"><label class="fl">Ancien mot de passe</label><input class="fi" type="password" name="mot_de_passe_actuel" required></div>
          <div class="fg"><label class="fl">Nouveau mot de passe</label><input class="fi" type="password" name="nouveau_mot_de_passe" required minlength="6"></div>
          <div class="fg"><label class="fl">Confirmer</label><input class="fi" type="password" name="confirmer_mot_de_passe" required></div>
          <button class="btn btn-primary btn-full btn-sm" type="submit" name="btn_changer_mdp"> Modifier</button>
        </form>
      </div>
    </div>
<?php layout_close(); ?>