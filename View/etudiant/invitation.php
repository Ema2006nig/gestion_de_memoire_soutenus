<?php
if (!isset($memoire) || !isset($profs)) {
    header('Location: dashboard.php');
    exit;
}
?>
<?php
$pageTitle   = "Invitation à co-écrire";
$pageSection = "Espace étudiant";
$activeRoute = "dashboard";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<div class="card" style="max-width:700px;margin:0 auto;">
      <div class="card-title"> Invitation à être co-auteur</div>
      <div class="alert alert-info">
        <strong>Mémoire :</strong> <?= htmlspecialchars($memoire['theme']) ?><br>
        <strong>Auteur principal :</strong> <?= htmlspecialchars($memoire['prenom'] . ' ' . $memoire['nom']) ?>
      </div>

      <form method="POST" enctype="multipart/form-data">
        <div class="fg">
          <label class="fl">Thème du mémoire *</label>
          <input class="fi" type="text" name="theme" value="<?= htmlspecialchars($memoire['theme']) ?>" required>
        </div>
        <div class="frow">
          <div class="fg">
            <label class="fl">Année académique *</label>
            <input class="fi" type="text" name="anneeAcademique" value="<?= htmlspecialchars($memoire['anneeAcademique']) ?>" required>
          </div>
          <div class="fg">
            <label class="fl">Centre de soutenance *</label>
            <select class="fsel" name="centre" required>
              <option value="">-- Centre --</option>
              <option value="AKPAKPA" <?= $memoire['centre']=='AKPAKPA'?'selected':'' ?>>AKPAKPA</option>
              <option value="AGLA" <?= $memoire['centre']=='AGLA'?'selected':'' ?>>AGLA</option>
              <option value="PORTO-NOVO" <?= $memoire['centre']=='PORTO-NOVO'?'selected':'' ?>>PORTO-NOVO</option>
              <option value="GBEGAMEY" <?= $memoire['centre']=='GBEGAMEY'?'selected':'' ?>>GBEGAMEY</option>
              <option value="CALAVI" <?= $memoire['centre']=='CALAVI'?'selected':'' ?>>CALAVI</option>
            </select>
          </div>
        </div>
        <div class="fg">
          <label class="fl">Professeur encadreur *</label>
          <select class="fsel" name="idProf" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($profs as $p): ?>
            <option value="<?= $p['idUtilisateur'] ?>" <?= ($memoire['idProf']==$p['idUtilisateur'])?'selected':'' ?>>
              Prof. <?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?> — <?= htmlspecialchars($p['specialite']??'') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Fichier PDF (optionnel, laissez vide pour garder l'actuel)</label>
          <input class="fi" type="file" name="fichierPdf" accept=".pdf">
          <?php if ($memoire['fichierPdf']): ?>
          <small>Fichier actuel : <a href="../../uploads/memoires/<?= $memoire['fichierPdf'] ?>" target="_blank">voir</a></small>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:.6rem;margin-top:1rem;">
          <button class="btn btn-success" type="submit" name="accepter"> Accepter et soumettre</button>
          <button class="btn btn-danger" type="submit" name="refuser" onclick="return confirm('Êtes-vous sûr de vouloir refuser ? Le mémoire sera supprimé.');"> Refuser</button>
          <a href="dashboard.php" class="btn btn-ghost">Annuler</a>
        </div>
      </form>
    </div>
<?php layout_close(); ?>