<?php
/**
 * Vue : Gestion des utilisateurs (Admin)
 * Données : $utilisateurs (array) contenant id, nom, prenom, email, role, date_inscription
 *          $roles possibles : etudiant, professeur, admin
 */
?>
<div class="entete-page">
    <div>
        <h1 class="entete-page__titre">Gestion des utilisateurs</h1>
        <p class="entete-page__sous">Ajouter, modifier ou supprimer des comptes</p>
    </div>
    <button class="btn btn--accent" data-ouvre-modal="modalAjoutUser">+ Nouvel utilisateur</button>
</div>

<div class="barre-recherche" style="margin-bottom: var(--espace-lg);">
    <span class="barre-recherche__icone">🔍</span>
    <input type="text" class="champ" id="rechercheUser" data-recherche="tableUtilisateurs" placeholder="Rechercher par nom, email...">
</div>

<div class="tableau-wrapper">
    <table class="tableau" id="tableUtilisateurs">
        <thead>
            <tr><th>ID</th><th>Nom complet</th><th>Email</th><th>Rôle</th><th>Inscrit le</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $user) : ?>
                <tr>
                    <td><?= (int)$user['id'] ?></td>
                    <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                    <td>
                        <button class="btn btn--sm btn--contour" data-ouvre-modal="modalEditUser" data-id="<?= $user['id'] ?>">✏️ Modifier</button>
                        <button class="btn btn--sm btn--danger" data-confirmer="Supprimer définitivement cet utilisateur ?" data-id="<?= $user['id'] ?>">🗑️ Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajout / Modification (simplifié) -->
<div id="modalAjoutUser" class="modal-fond">
    <div class="modal">
        <div class="modal__entete">
            <h3 class="modal__titre">Ajouter un utilisateur</h3>
            <span class="modal__fermer">&times;</span>
        </div>
        <form method="POST" action="index.php?route=adminUser&action=ajouter" data-valider="true">
            <div class="modal__corps">
                <div class="groupe-champ"><label class="label label--requis">Prénom</label><input type="text" name="prenom" class="champ" required></div>
                <div class="groupe-champ"><label class="label label--requis">Nom</label><input type="text" name="nom" class="champ" required></div>
                <div class="groupe-champ"><label class="label label--requis">Email</label><input type="email" name="email" class="champ" required data-type="email"></div>
                <div class="groupe-champ"><label class="label label--requis">Mot de passe</label><input type="password" name="mdp" class="champ" required data-min="6"></div>
                <div class="groupe-champ"><label class="label">Rôle</label><select name="role" class="champ"><option value="etudiant">Étudiant</option><option value="professeur">Professeur</option><option value="admin">Administrateur</option></select></div>
            </div>
            <div class="modal__pied"><button type="submit" class="btn btn--primaire">Créer</button></div>
        </form>
    </div>
</div>