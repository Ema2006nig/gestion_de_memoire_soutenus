<h1 class="mb-4">Mes mémoires</h1>

<div class="card">

<?php if (empty($liste)): ?>

```
<p>Aucun mémoire déposé pour le moment.</p>
```

<?php else: ?>

```
<table>
    <thead>
        <tr>
            <th>Titre</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($liste as $m): ?>

        <?php
            $st = $m['statut'] ?? '';

            if (in_array($st, ['en_ligne', 'valide_prof', 'valide_direction'])) {
                $cls = 'ok';
            } elseif ($st === 'refuse_prof') {
                $cls = 'bad';
            } else {
                $cls = 'warn';
            }
        ?>

        <tr>
            <td><?= e($m['titre'] ?? '') ?></td>

            <td><?= e($m['date_soumission'] ?? '') ?></td>

            <td>
                <span class="badge <?= $cls ?>">
                    <?= e($st) ?>
                </span>
            </td>

            <td>
                <a
                    class="btn alt"
                    href="<?= BASE_URL ?>/index.php?route=lire&id=<?= (int)($m['id'] ?? 0) ?>">
                    Voir
                </a>
            </td>
        </tr>

    <?php endforeach; ?>

    </tbody>
</table>
```

<?php endif; ?>

</div>
