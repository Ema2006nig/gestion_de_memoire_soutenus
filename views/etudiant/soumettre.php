<div class="card">
  <h1>Deposer un memoire</h1>
  <form method="post" action="<?= BASE_URL ?>/index.php?route=soumettre" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Titre</label><input type="text" name="titre" required maxlength="255">
    <label>Resume</label><textarea name="resume" maxlength="2000"></textarea>
    <label>Professeur encadrant</label>
    <select name="professeur_id" required>
      <option value="">-- choisir --</option>
      <?php foreach ($profs as $p): ?>
        <option value="<?= (int)$p['id'] ?>"><?= e($p['nom']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Fichier PDF (max 20 Mo)</label>
    <input type="file" name="fichier" accept="application/pdf" required>
    <br><br><button class="btn" type="submit">Deposer</button>
  </form>
</div>
