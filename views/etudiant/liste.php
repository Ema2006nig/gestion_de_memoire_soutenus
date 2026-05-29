<h1>Mes memoires</h1>
<div class="card">
<?php if (!$liste): ?><p>Aucun memoire depose.</p>
<?php else: ?>
<table><tr><th>Titre</th><th>Date</th><th>Statut</th><th></th></tr>
<?php foreach ($liste as $m):
  $st = $m['statut'];
  $cls = in_array($st,['en_ligne','valide_prof','valide_direction'])?'ok':($st==='refuse_prof'?'bad':'warn');
?>
  <tr>
    <td><?= e($m['titre']) ?></td>
    <td><?= e($m['date_soumission']) ?></td>
    <td><span class="badge <?= $cls ?>"><?= e($st) ?></span></td>
    <td><a class="btn alt" href="<?= BASE_URL ?>/index.php?route=lire&id=<?= (int)$m['id'] ?>">Voir</a></td>
  </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
