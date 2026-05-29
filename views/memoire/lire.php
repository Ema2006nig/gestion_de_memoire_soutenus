<?php $u = current_user(); ?>
<h1><?= e($m['titre']) ?></h1>
<div class="card">
  <p><b>Etudiant :</b> <?= e($m['etudiant_nom']) ?> ·
     <b>Encadrant :</b> <?= e($m['prof_nom'] ?? '-') ?> ·
     <b>Statut :</b> <span class="badge"><?= e($m['statut']) ?></span></p>
  <?php if (!empty($m['resume'])): ?><p style="margin-top:8px"><?= nl2br(e($m['resume'])) ?></p><?php endif; ?>
</div>

<div class="card">
  <h2>Lecture du document</h2>
  <div class="pdf-wrap">
    <iframe id="pdfViewer"
      src="<?= BASE_URL ?>/index.php?route=pdf&id=<?= (int)$m['id'] ?>#toolbar=0&navpanes=0"
      sandbox="allow-same-origin allow-scripts"></iframe>
    <div class="shield"></div>
  </div>
  <p style="font-size:.8rem;color:#64748b;margin-top:6px">
    Telechargement, impression et capture d'ecran desactives. Document confidentiel.
  </p>
</div>

<div class="card">
  <h2>Commentaires</h2>
  <?php if (!$coms): ?><p>Aucun commentaire.</p><?php endif; ?>
  <?php foreach ($coms as $c): ?>
    <div style="border-bottom:1px solid #eee;padding:8px 0">
      <b><?= e($c['auteur_nom']) ?></b>
      <span class="badge"><?= e($c['auteur_role']) ?></span>
      <span style="color:#64748b;font-size:.8rem">· <?= e($c['cree_le']) ?></span>
      <p><?= nl2br(e($c['contenu'])) ?></p>
    </div>
  <?php endforeach; ?>

  <?php
    $peutCommenter = $u && (
      ($u['role']==='professeur' && (int)$m['professeur_id']===$u['id']) ||
      ($u['role']==='commentateur' && $m['statut']==='en_ligne') ||
      ($u['role']==='direction')
    );
  ?>
  <?php if ($peutCommenter): ?>
    <form method="post" action="<?= BASE_URL ?>/index.php?route=commenter" style="margin-top:14px">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="memoire_id" value="<?= (int)$m['id'] ?>">
      <label>Ajouter un commentaire</label>
      <textarea name="contenu" required maxlength="1000"></textarea>
      <br><button class="btn">Envoyer</button>
    </form>
  <?php endif; ?>
</div>
