<h1>Memoires a encadrer</h1>
<div class="card">
<?php if (!$liste): ?><p>Rien a traiter.</p>
<?php else: ?>
<table><tr><th>Etudiant</th><th>Titre</th><th>Statut</th><th>Actions</th></tr>
<?php foreach ($liste as $m): ?>
  <tr>
    <td><?= e($m['etudiant_nom']) ?></td>
    <td><?= e($m['titre']) ?></td>
    <td><span class="badge"><?= e($m['statut']) ?></span></td>
    <td>
      <a class="btn alt" href="<?= BASE_URL ?>/index.php?route=lire&id=<?= (int)$m['id'] ?>">Lire</a>
      <?php if ($m['statut']==='soumis'): ?>
        <form method="post" action="<?= BASE_URL ?>/index.php?route=prof_decider" style="display:inline">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <button class="btn success" name="action" value="valider">Valider</button>
          <button class="btn danger"  name="action" value="refuser">Refuser</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
