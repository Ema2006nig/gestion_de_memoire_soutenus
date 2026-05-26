<?php
/**
 * Vue : Journal de modération (Admin)
 * Données : $logs (array) avec id, action, utilisateur_nom, cible_type, cible_id, details, date
 */
?>
<div class="entete-page">
    <div>
        <h1 class="entete-page__titre">Journal de modération</h1>
        <p class="entete-page__sous">Historique complet des actions administratives et validations</p>
    </div>
    <div class="filtres">
        <select id="filtreAction" class="champ" style="width:auto;">
            <option value="">Toutes les actions</option>
            <option value="APPROUVER">Approbations</option>
            <option value="REJETER">Rejets</option>
            <option value="SUPPRIMER">Suppressions</option>
            <option value="MODIFIER_ROLE">Modifications rôle</option>
        </select>
        <input type="text" id="filtreUtilisateur" class="champ" placeholder="Filtrer par utilisateur" style="width:200px;">
    </div>
</div>

<div class="tableau-wrapper">
    <table class="tableau" id="tableLogs">
        <thead>
            <tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Cible</th><th>Détails</th></tr>
        </thead>
        <tbody>
            <?php if (empty($logs)) : ?>
                <tr><td colspan="5" class="etat-vide">Aucune entrée de modération.</td></tr>
            <?php else : ?>
                <?php foreach ($logs as $log) : ?>
                    <tr data-action="<?= htmlspecialchars($log['action']) ?>" data-utilisateur="<?= htmlspecialchars($log['utilisateur_nom']) ?>">
                        <td><?= date('d/m/Y H:i:s', strtotime($log['date'])) ?></td>
                        <td><?= htmlspecialchars($log['utilisateur_nom']) ?></td>
                        <td>
                            <span class="badge badge--info">
                                <?= htmlspecialchars($log['action']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($log['cible_type']) ?> #<?= (int)$log['cible_id'] ?></td>
                        <td><?= nl2br(htmlspecialchars($log['details'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Filtrage client simple
    const filtresAction = document.getElementById('filtreAction');
    const filtreUtilisateur = document.getElementById('filtreUtilisateur');
    const lignes = document.querySelectorAll('#tableLogs tbody tr');

    function filtrerLogs() {
        const action = filtresAction.value;
        const user = filtreUtilisateur.value.toLowerCase();
        lignes.forEach(ligne => {
            const actionLigne = ligne.dataset.action || '';
            const userLigne = (ligne.dataset.utilisateur || '').toLowerCase();
            let visible = true;
            if (action && actionLigne !== action) visible = false;
            if (user && !userLigne.includes(user)) visible = false;
            ligne.style.display = visible ? '' : 'none';
        });
    }
    filtresAction.addEventListener('change', filtrerLogs);
    filtreUtilisateur.addEventListener('input', filtrerLogs);
</script>