<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle = "Upload ancien mémoires via CSV";
$de        = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$profs     = Professeur::listerTous();
$etudiants = $de->listerEtudiants();
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Import par fichier CSV";
$pageSection = "Direction des études";
$activeRoute = "imports";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <div class="card" style="max-width:800px;margin:0 auto;">
      <div class="card-title"> Uploader des anciens mémoires via CSV</div>
      <div class="alert alert-info"> Permet d'uploader plusieurs mémoires pour différents étudiants et professeurs en une seule fois.</div>

      <div class="alert alert-warning" style="margin-bottom:1.5rem;">
        <strong>Format CSV requis :</strong><br>
        Le fichier CSV doit contenir les colonnes suivantes dans l'ordre :<br>
        <code>nom_fichier,id_etudiant,id_prof,annee_academique,centre,theme</code><br><br>
        <strong>Centres disponibles :</strong> AKPAKPA, AGLA, PORTO-NOVO, GBEGAMEY, CALAVI<br><br>
        <strong>Exemple :</strong><br>
        <code>memoire_jean.pdf,2021INFO0234,PROF001,2021-2022,AKPAKPA,Système de gestion scolaire</code><br>
        <code>memoire_marie.pdf,2021INFO0198,PROF002,2022-2023,AGLA,Application mobile</code>
      </div>

      <form method="POST" action="../../Controller/traitement_auth.php" enctype="multipart/form-data">
        <div class="fg">
          <label class="fl">Fichier CSV des mémoires *</label>
          <input class="fi" type="file" name="fichier_csv" accept=".csv" required>
          <small style="color:var(--gray-400);">Format CSV uniquement</small>
        </div>

        <div class="fg">
          <label class="fl">Fichiers PDF des mémoires *</label>
          <input class="fi" type="file" name="fichiers_pdf[]" accept=".pdf" multiple required>
          <small style="color:var(--gray-400);">Sélectionnez tous les fichiers PDF correspondants aux noms dans le CSV. Max 10 Mo par fichier.</small>
        </div>

        <div class="alert alert-success"> Ces mémoires seront publiés immédiatement avec statut VALIDE</div>
        <div style="display:flex;gap:.6rem;">
          <button class="btn btn-primary" type="submit" name="btn_upload_ancien_csv" style="flex:1;justify-content:center;"> Enregistrer les mémoires</button>
          <a href="dashboard.php" class="btn btn-ghost"> Annuler</a>
        </div>
      </form>
    </div>
<?php layout_close(); ?>