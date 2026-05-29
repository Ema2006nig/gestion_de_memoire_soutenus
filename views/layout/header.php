<?php $u = current_user(); ?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($titre ?? 'Gestion des memoires') ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="no-select">
<header class="nav">
  <div>
    <a href="<?= BASE_URL ?>/index.php?route=accueil"><strong>Memoires</strong></a>
    <?php if ($u): ?>
      <a href="<?= BASE_URL ?>/index.php?route=accueil">Bibliotheque</a>
      <?php if ($u['role']==='etudiant'): ?>
        <a href="<?= BASE_URL ?>/index.php?route=soumettre">Deposer</a>
        <a href="<?= BASE_URL ?>/index.php?route=mes_memoires">Mes memoires</a>
      <?php endif; ?>
      <?php if ($u['role']==='professeur'): ?>
        <a href="<?= BASE_URL ?>/index.php?route=prof_liste">A encadrer</a>
      <?php endif; ?>
      <?php if ($u['role']==='direction'): ?>
        <a href="<?= BASE_URL ?>/index.php?route=direction">Supervision</a>
      <?php endif; ?>
      <?php if ($u['role']==='service_technique'): ?>
        <a href="<?= BASE_URL ?>/index.php?route=admin_users">Utilisateurs</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="user">
    <?php if ($u): ?>
      <?= e($u['nom']) ?> (<?= e($u['role']) ?>) ·
      <a href="<?= BASE_URL ?>/index.php?route=logout">Deconnexion</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/index.php?route=login">Connexion</a>
    <?php endif; ?>
  </div>
</header>
<main>
<?php if ($m = flash('succes')): ?><div class="flash ok"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('erreur')): ?><div class="flash err"><?= e($m) ?></div><?php endif; ?>
