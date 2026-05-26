<?php
/**
 * Vue : Dashboard Admin
 * Données attendues : $stats (array) contenant :
 *   - total_memoires, total_etudiants, total_professeurs, memoires_en_attente,
 *     memoires_valides, memoires_rejetes, commentaires_total, activite_recente (array)
 */
?>
<div class="entete-page">
    <div>
        <h1 class="entete-page__titre">Tableau de bord</h1>
        <p class="entete-page__sous">Vue d'ensemble de la plateforme</p>
    </div>
</div>

<div class="stat-grille">
    <div class="stat-carte">
        <div class="stat-carte__icone stat-carte__icone--bleu">📄</div>
        <div>
            <div class="stat-carte__valeur"><?= (int)($stats['total_memoires'] ?? 0) ?></div>
            <div class="stat-carte__label">Mémoires déposés</div>
        </div>
    </div>
    <div class="stat-carte">
        <div class="stat-carte__icone stat-carte__icone--vert">👨‍🎓</div>
        <div>
            <div class="stat-carte__valeur"><?= (int)($stats['total_etudiants'] ?? 0) ?></div>
            <div class="stat-carte__label">Étudiants inscrits</div>
        </div>
    </div>
    <div class="stat-carte">
        <div class="stat-carte__icone stat-carte__icone--orange">👩‍🏫</div>
        <div>
            <div class="stat-carte__valeur"><?= (int)($stats['total_professeurs'] ?? 0) ?></div>
            <div class="stat-carte__label">Professeurs</div>
        </div>
    </div>
    <div class="stat-carte">
        <div class="stat-carte__icone stat-carte__icone--rouge">⏳</div>
        <div>
            <div class="stat-carte__valeur"><?= (int)($stats['memoires_en_attente'] ?? 0) ?></div>
            <div class="stat-carte__label">En attente de validation</div>
        </div>
    </div>
</div>

<div class="stat-grille" style="grid-template-columns: repeat(3,1fr);">
    <div class="carte">
        <h3 class="carte__titre" style="font-size:var(--taille-lg);">Validés</h3>
        <p class="stat-carte__valeur" style="color:var(--couleur-succes);"><?= (int)($stats['memoires_valides'] ?? 0) ?></p>
    </div>
    <div class="carte">
        <h3 class="carte__titre" style="font-size:var(--taille-lg);">Rejetés</h3>
        <p class="stat-carte__valeur" style="color:var(--couleur-danger);"><?= (int)($stats['memoires_rejetes'] ?? 0) ?></p>
    </div>
    <div class="carte">
        <h3 class="carte__titre" style="font-size:var(--taille-lg);">Commentaires</h3>
        <p class="stat-carte__valeur"><?= (int)($stats['commentaires_total'] ?? 0) ?></p>
    </div>
</div>

<div class="carte">
    <h2 class="carte__titre">Activité récente</h2>
    <div class="tableau-wrapper">
        <table class="tableau">
            <thead>
                <tr><th>Action</th><th>Utilisateur</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php if (empty($stats['activite_recente'])) : ?>
                    <tr><td colspan="3" class="etat-vide">Aucune activité récente</td></tr>
                <?php else : ?>
                    <?php foreach ($stats['activite_recente'] as $act) : ?>
                        <tr>
                            <td><?= htmlspecialchars($act['action']) ?></td>
                            <td><?= htmlspecialchars($act['utilisateur']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($act['date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>