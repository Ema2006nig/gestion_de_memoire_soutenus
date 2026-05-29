<h1>Gestion des utilisateurs</h1>
<div class="card">
  <h2>Creer un compte</h2>
  <form method="post" action="<?= BASE_URL ?>/index.php?route=admin_users">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Nom</label><input name="nom" required>
    <label>Email</label><input type="email" name="email" required>
    <label>Mot de passe (min 8)</label><input type="password" name="mot_de_passe" required minlength="8">
    <label>Role</label>
    <select name="role">
      <option value="etudiant">Etudiant</option>
      <option value="professeur">Professeur</option>
      <option value="direction">Direction</option>
      <option value="commentateur">Commentateur</option>
      <option value="service_technique">Service technique</option>
    </select>
    <label>Matricule (etudiant)</label><input name="matricule">
    <label>Grade (professeur)</label><input name="grade">
    <br><br><button class="btn">Creer</button>
  </form>
</div>
<div class="card">
<table><tr><th>Nom</th><th>Email</th><th>Role</th><th>Statut</th><th></th></tr>
<?php foreach ($liste as $u): ?>
<tr>
  <td><?= e($u['nom']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td>
  <td><?= $u['actif'] ? '<span class="badge ok">actif</span>' : '<span class="badge bad">desactive</span>' ?></td>
  <td>
    <form method="post" action="<?= BASE_URL ?>/index.php?route=admin_toggle" style="display:inline">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
      <button class="btn alt"><?= $u['actif'] ? 'Desactiver' : 'Activer' ?></button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>
</div>
