<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Mon profil";
$de        = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Mon profil";
$pageSection = "Direction des études";
$activeRoute = "profil";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-title"> Mon profil</div>
      
      <!-- Informations personnelles -->
      <div style="background:#f9f9f9;padding:1rem;border-radius:8px;margin-bottom:1.5rem;">
        <h3 style="font-size:.9rem;font-weight:700;color:var(--navy);margin-bottom:.8rem;">Informations personnelles</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.8rem;">
          <div>
            <label style="font-size:.7rem;color:var(--gray-500);display:block;margin-bottom:.2rem;">Matricule</label>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($_SESSION['idUtilisateur']) ?></div>
          </div>
          <div>
            <label style="font-size:.7rem;color:var(--gray-500);display:block;margin-bottom:.2rem;">Nom</label>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($_SESSION['nom']) ?></div>
          </div>
          <div>
            <label style="font-size:.7rem;color:var(--gray-500);display:block;margin-bottom:.2rem;">Prénom</label>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($_SESSION['prenom']) ?></div>
          </div>
          <div>
            <label style="font-size:.7rem;color:var(--gray-500);display:block;margin-bottom:.2rem;">Email</label>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
          </div>
          <div>
            <label style="font-size:.7rem;color:var(--gray-500);display:block;margin-bottom:.2rem;">Rôle</label>
            <div style="font-size:.82rem;font-weight:600;color:var(--navy);"><?= htmlspecialchars($_SESSION['role']) ?></div>
          </div>
        </div>
      </div>

      <!-- Formulaire de changement de mot de passe -->
      <h3 style="font-size:.9rem;font-weight:700;color:var(--navy);margin-bottom:.8rem;">Changer le mot de passe</h3>
      <form method="POST" action="../../Controller/traitement_auth.php">
        <div class="fg">
          <label class="fl">Mot de passe actuel</label>
          <input class="fi" type="password" name="mot_de_passe_actuel" required>
        </div>
        <div class="fg">
          <label class="fl">Nouveau mot de passe</label>
          <input class="fi" type="password" name="nouveau_mot_de_passe" required>
        </div>
        <div class="fg">
          <label class="fl">Confirmer le nouveau mot de passe</label>
          <input class="fi" type="password" name="confirmer_mot_de_passe" required>
        </div>
        <button class="btn btn-primary" type="submit" name="btn_changer_mdp"> Changer le mot de passe</button>
      </form>
    </div>
<?php layout_close(); ?>