<h1>Supervision</h1>
<div class="card">
  <h2>Statistiques</h2>
  <div class="stat-grid">
    <div class="stat"><b><?= (int)$stats['total'] ?></b>Total</div>
    <div class="stat"><b><?= (int)$stats['soumis'] ?></b>Soumis</div>
    <div class="stat"><b><?= (int)$stats['valide_prof'] ?></b>Valides prof.</div>
    <div class="stat"><b><?= (int)$stats['refuse_prof'] ?></b>Refuses</div>
    <div class="stat"><b><?= (int)$stats['en_ligne'] ?></b>En ligne</div>
  </div>
</div>
<div class="card">
<table><tr><th>Etudiant</th><th>Titre</th><th>Statut</th><th>Actions</th></tr>
<?php foreach ($liste as $m): ?>
  <tr>
    <td><?= e($m['etudiant_nom']) ?></td>
    <td><?= e($m['titre']) ?></td>
    <td><span class="badge"><?= e($m['statut']) ?></span></td>
    <td>
      <a class="btn alt" href="<?= BASE_URL ?>/index.php?route=lire&id=<?= (int)$m['id'] ?>">Lire</a>
      <?php if ($m['statut']==='valide_prof'): ?>
        <form method="post" action="<?= BASE_URL ?>/index.php?route=direction_valider" style="display:inline">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <button class="btn success">Mettre en ligne</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>
</table>
</div>
