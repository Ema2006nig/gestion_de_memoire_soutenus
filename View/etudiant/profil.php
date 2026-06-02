<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();
require_once __DIR__ . '/../../classes/Etudiant.php';
require_once __DIR__ . '/../../classes/EtudiantConsultant.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Mon profil";
$role = $_SESSION['role'];
$nbNotifs = Notification::compterNonLus($_SESSION['idCompte']);

// Récupérer les informations de l'utilisateur
if ($role === 'ETUDIANT') {
    $utilisateur = Etudiant::trouverParId($_SESSION['idUtilisateur']);
} else {
    $utilisateur = EtudiantConsultant::trouverParId($_SESSION['idUtilisateur']);
}
?>
<?php
$pageTitle   = "Mon profil";
$pageSection = "Espace étudiant";
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

    <div class="banner" style="background:linear-gradient(135deg,#1b998b,#2dc4b2)">
      <div class="banner-title">Mon profil <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?></div>
      <div class="banner-sub">Gérez vos informations personnelles</div>
    </div>

    <div class="card">
      <div class="card-title"> Informations personnelles</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <div>
          <div class="fg">
            <label class="fl">Matricule</label>
            <input class="fi" type="text" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getIdUtilisateur() : '') ?>" readonly>
          </div>
          <div class="fg">
            <label class="fl">Nom</label>
            <input class="fi" type="text" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getNom() : '') ?>" readonly>
          </div>
          <div class="fg">
            <label class="fl">Prénom</label>
            <input class="fi" type="text" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getPrenom() : '') ?>" readonly>
          </div>
          <div class="fg">
            <label class="fl">Email</label>
            <input class="fi" type="email" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getEmail() : '') ?>" readonly>
          </div>
        </div>
        <div>
          <div class="fg">
            <label class="fl">Date de naissance</label>
            <input class="fi" type="date" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getDateNaissance() : '') ?>" readonly>
          </div>
          <div class="fg">
            <label class="fl">Filière</label>
            <input class="fi" type="text" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getFiliere() : '') ?>" readonly>
          </div>
          <div class="fg">
            <label class="fl">Option</label>
            <input class="fi" type="text" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getOption() : '') ?>" readonly>
          </div>
          <div class="fg">
            <label class="fl">Niveau</label>
            <input class="fi" type="text" value="<?= htmlspecialchars($utilisateur ? $utilisateur->getNiveau() : '') ?>" readonly>
          </div>
        </div>
      </div>
      <div style="margin-top:1rem;padding:.8rem;background:var(--gray-50);border-radius:var(--radius-sm);font-size:.75rem;color:var(--gray-600);">
        <?= icon('info', 'w-4 h-4 inline-block align-text-bottom') ?> Pour modifier vos informations personnelles, veuillez contacter le DE.
      </div>
    </div>

    <div class="card">
      <div class="card-title"> Changer le mot de passe</div>
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