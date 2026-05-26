<?php
/**
 * Vue : Liste des mémoires à valider (Professeur)
 * Données attendues : $memoires (array) contenant chaque mémoire avec :
 *   - id, titre, etudiant_nom, etudiant_prenom, date_depot, statut, fichier_chemin
 * Utilise les classes CSS du thème principal.
 */
?>
<div class="entete-page">
    <div>
        <h1 class="entete-page__titre">Mémoires à valider</h1>
        <p class="entete-page__sous">Consultez et approuvez les travaux soumis par les étudiants</p>
    </div>
    <div class="barre-recherche">
        <span class="barre-recherche__icone">🔍</span>
        <input type="text" class="champ" id="rechercheMemoire" data-recherche="tableValidation" placeholder="Rechercher par titre, étudiant...">
    </div>
</div>

<div class="tableau-wrapper">
    <table class="tableau" id="tableValidation">
        <thead>
            <tr>
                <th>Titre du mémoire</th>
                <th>Étudiant</th>
                <th>Date de dépôt</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($memoires)) : ?>
                <tr class="ligne-vide">
                    <td colspan="5" style="text-align:center; padding:2rem;">
                        Aucun mémoire en attente de validation.
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($memoires as $memoire) : ?>
                    <tr>
                        <td><?= htmlspecialchars($memoire['titre']) ?></td>
                        <td><?= htmlspecialchars($memoire['etudiant_prenom'] . ' ' . $memoire['etudiant_nom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($memoire['date_depot'])) ?></td>
                        <td>
                            <span class="badge badge--attente">En attente</span>
                        </td>
                        <td class="actions">
                            <a href="index.php?route=relecture&id=<?= $memoire['id'] ?>" class="btn btn--sm btn--accent">📖 Relecture</a>
                            <button class="btn btn--sm btn--contour" data-ouvre-modal="modalApprouver" data-id="<?= $memoire['id'] ?>">✅ Approuver</button>
                            <button class="btn btn--sm btn--danger" data-ouvre-modal="modalRejeter" data-id="<?= $memoire['id'] ?>">❌ Rejeter</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Approuver -->
<div id="modalApprouver" class="modal-fond">
    <div class="modal">
        <div class="modal__entete">
            <h3 class="modal__titre">Approuver le mémoire</h3>
            <span class="modal__fermer">&times;</span>
        </div>
        <form method="POST" action="index.php?route=validerMemoire">
            <input type="hidden" name="id" id="approuverId" value="">
            <div class="modal__corps">
                <p>Confirmez-vous l'approbation de ce mémoire ?</p>
            </div>
            <div class="modal__pied">
                <button type="button" class="btn btn--contour modal__fermer">Annuler</button>
                <button type="submit" name="action" value="approuver" class="btn btn--succes">Oui, approuver</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rejeter -->
<div id="modalRejeter" class="modal-fond">
    <div class="modal">
        <div class="modal__entete">
            <h3 class="modal__titre">Rejeter le mémoire</h3>
            <span class="modal__fermer">&times;</span>
        </div>
        <form method="POST" action="index.php?route=validerMemoire">
            <input type="hidden" name="id" id="rejeterId" value="">
            <div class="modal__corps">
                <div class="groupe-champ">
                    <label class="label label--requis">Motif du rejet</label>
                    <textarea name="motif" class="champ" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal__pied">
                <button type="button" class="btn btn--contour modal__fermer">Annuler</button>
                <button type="submit" name="action" value="rejeter" class="btn btn--danger">Rejeter</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Transmission des ID mémoire aux modals
    document.querySelectorAll('[data-ouvre-modal="modalApprouver"]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('approuverId').value = btn.dataset.id;
        });
    });
    document.querySelectorAll('[data-ouvre-modal="modalRejeter"]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('rejeterId').value = btn.dataset.id;
        });
    });
</script>