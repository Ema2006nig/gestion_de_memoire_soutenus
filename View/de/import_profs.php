<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Import CSV - Professeurs";
$nbNotifs = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Import des professeurs";
$pageSection = "Direction des études";
$activeRoute = "imports";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<div class="sidebar">
    <a href="dashboard.php" class="sitem"><div class="si"></div><div class="sl">Accueil</div></a>
    <a href="liste_etudiants.php" class="sitem"><div class="si"></div><div class="sl">Étudiants</div></a>
    <a href="liste_profs.php" class="sitem"><div class="si"></div><div class="sl">Profs</div></a>
    <a href="upload_ancien.php" class="sitem"><div class="si"></div><div class="sl">Upload</div></a>
    <a href="../shared/liste_memoires.php" class="sitem"><div class="si"></div><div class="sl">Mémoires</div></a>
  </div>
  <?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-title"> Import CSV - Professeurs</div>
      
      <div class="alert alert-info" style="margin-bottom:1.5rem;">
        <strong>Format CSV requis :</strong><br>
        Le fichier CSV doit contenir les colonnes suivantes dans l'ordre :<br>
        <code>idUtilisateur,nom,prenom,email,specialite</code><br><br>
        <strong>Exemple :</strong><br>
        <code>PROF001,Martin,Pierre,pierre@uatm.ga,Génie Logiciel</code><br>
        <code>PROF002,Dubois,Sophie,sophie@uatm.ga,Finance</code>
      </div>

      <form method="POST" action="../../Controller/traitement_auth.php" enctype="multipart/form-data">
        <div class="frow">
          <div class="fg">
            <label class="fl">Fichier CSV *</label>
            <input class="fi" type="file" name="fichier_csv" accept=".csv" required>
            <small style="color:var(--gray-400);">Fichier .csv uniquement</small>
          </div>
          <div class="fg" style="display:flex;align-items:flex-end;">
            <button class="btn btn-primary btn-full" type="submit" name="btn_import_profs">Importer</button>
          </div>
        </div>
      </form>

      <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gray-200);">
        <a href="liste_profs.php" class="btn btn-ghost">← Retour à la liste</a>
      </div>
    </div>
<?php layout_close(); ?>