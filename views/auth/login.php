<div class="card" style="max-width:420px;margin:60px auto">
  <h1>Connexion</h1>
  <form method="post" action="<?= BASE_URL ?>/index.php?route=login">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Mot de passe</label>
    <input type="password" name="mot_de_passe" required>
    <br><br>
    <button class="btn" type="submit">Se connecter</button>
  </form>
  <p style="margin-top:14px;font-size:.85rem;color:#64748b">
    Comptes test (apres install.php) : etudiant@univ.local / prof@univ.local /
    direction@univ.local / commentateur@univ.local / admin@univ.local — mot de passe : <code>Passw0rd!</code>
  </p>
</div>
