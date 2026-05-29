<h1>Bibliotheque des memoires en ligne</h1>
<form method="get" action="<?= BASE_URL ?>/index.php" class="card" style="display:flex;gap:10px">
  <input type="hidden" name="route" value="accueil">
  <input name="q" value="<?= e($q) ?>" placeholder="Rechercher par titre...">
  <button class="btn">Chercher</button>
</form>
<div class="card">
<?php if (!$liste): ?><p>Aucun memoire en ligne.</p>
<?php else: ?>
<table><tr><th>Titre</th><th>Etudiant</th><th>Date</th><th></th></tr>
<?php foreach ($liste as $m): ?>
  <tr>
    <td><?= e($m['titre']) ?></td>
    <td><?= e($m['etudiant_nom']) ?></td>
    <td><?= e($m['date_soumission']) ?></td>
    <td><a class="btn" href="<?= BASE_URL ?>/index.php?route=lire&id=<?= (int)$m['id'] ?>">Lire</a></td>
  </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
