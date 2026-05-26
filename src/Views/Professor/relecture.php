<?php
/**
 * Vue : Relecture d'un mémoire (Professeur)
 * Données attendues : $memoire (array) avec id, titre, contenu_texte, fichier_pdf, etudiant_nom, etudiant_prenom, date_depot, commentaires_existants (array)
 *                    $commentaires (array) liste des commentaires déjà postés
 */
?>
<div class="entete-page">
    <div>
        <h1 class="entete-page__titre">Relecture : <?= htmlspecialchars($memoire['titre']) ?></h1>
        <p class="entete-page__sous">Étudiant : <?= htmlspecialchars($memoire['etudiant_prenom'] . ' ' . $memoire['etudiant_nom']) ?> | Déposé le <?= date('d/m/Y', strtotime($memoire['date_depot'])) ?></p>
    </div>
    <div>
        <a href="index.php?route=listeValidation" class="btn btn--contour">← Retour à la liste</a>
    </div>
</div>

<div class="carte" style="margin-bottom: var(--espace-xl);">
    <h2 class="carte__titre">Contenu du mémoire</h2>
    <div style="background: var(--fond-page); padding: var(--espace-lg); border-radius: var(--radius-md);">
        <?php if (!empty($memoire['contenu_texte'])) : ?>
            <?= nl2br(htmlspecialchars($memoire['contenu_texte'])) ?>
        <?php else : ?>
            <p class="alerte alerte--info">Aucun résumé texte fourni.</p>
        <?php endif; ?>
    </div>
    <?php if (!empty($memoire['fichier_pdf'])) : ?>
        <div style="margin-top: var(--espace-lg);">
            <a href="index.php?route=telechargerMemoire&id=<?= $memoire['id'] ?>" class="btn btn--accent">📄 Télécharger le PDF complet</a>
        </div>
    <?php endif; ?>
</div>

<div class="carte">
    <h2 class="carte__titre">Commentaires de relecture</h2>
    <div id="commentairesListe" style="margin-bottom: var(--espace-lg);">
        <?php if (empty($commentaires)) : ?>
            <p class="alerte alerte--info">Aucun commentaire pour l'instant.</p>
        <?php else : ?>
            <?php foreach ($commentaires as $com) : ?>
                <div style="border-left: 3px solid var(--couleur-accent); padding-left: var(--espace-md); margin-bottom: var(--espace-md);">
                    <strong><?= htmlspecialchars($com['auteur_nom']) ?></strong> <small>(<?= date('d/m/Y H:i', strtotime($com['date'])) ?>)</small>
                    <p><?= nl2br(htmlspecialchars($com['contenu'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form method="POST" action="index.php?route=ajouterCommentaire" id="formCommentaire" data-valider="true">
        <input type="hidden" name="memoire_id" value="<?= $memoire['id'] ?>">
        <div class="groupe-champ">
            <label class="label label--requis">Ajouter un commentaire</label>
            <textarea name="contenu" class="champ" rows="4" required data-min="5" placeholder="Vos remarques, suggestions ou corrections..."></textarea>
        </div>
        <div class="modal__pied" style="padding-left:0;">
            <button type="submit" class="btn btn--primaire">Publier le commentaire</button>
        </div>
    </form>
</div>

<!-- Option actions globales -->
<div style="display: flex; gap: var(--espace-md); justify-content: flex-end; margin-top: var(--espace-xl);">
    <button class="btn btn--succes" data-ouvre-modal="modalApprouver">✅ Approuver ce mémoire</button>
    <button class="btn btn--danger" data-ouvre-modal="modalRejeter">❌ Rejeter ce mémoire</button>
</div>

<!-- Modals (identiques à liste_validation.php) -->
<div id="modalApprouver" class="modal-fond">[...]</div>
<div id="modalRejeter" class="modal-fond">[...]</div>