<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/NotificationRepository.php';

$pageTitle = "Gestion de la visibilité des mémoires";
$memoires = MemoireRepository::findAll();
$nbNotifs = NotificationRepository::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Gestion de la visibilité";
$pageSection = "Direction des études";
$activeRoute = "visibilite";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-title"> Gestion de la visibilité des mémoires</div>
      <p style="font-size:.78rem;color:var(--gray-500);margin-bottom:1rem;">
        Cochez les mémoires que vous souhaitez rendre visibles sur l'application. Les mémoires décochés ne seront pas affichés dans la liste publique.
      </p>

      <form method="POST" action="../../Controller/traitement_auth.php">
        <div class="table-wrap" style="border:1px solid var(--gray-200);border-radius:var(--radius-sm);overflow:hidden;">
          <table>
            <thead>
              <tr>
                <th style="width:50px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                <th>Thème</th>
                <th>Étudiant</th>
                <th>Année</th>
                <th>Statut</th>
                <th>Visible</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($memoires as $m): ?>
            <tr>
              <td><input type="checkbox" name="memoires_visibles[]" value="<?= $m['idMemoire'] ?>" <?= $m['visible'] ? 'checked' : '' ?>></td>
              <td><?= htmlspecialchars($m['theme']) ?></td>
              <td><?= htmlspecialchars($m['prenomEtudiant'] . ' ' . $m['nomEtudiant']) ?></td>
              <td><?= htmlspecialchars($m['anneeAcademique']) ?></td>
              <td>
                <?php
                $badgeClass = match($m['statut']) {
                    'VALIDE'     => 'b-ok',
                    'EN_ATTENTE' => 'b-wait',
                    'REJETE'     => 'b-ko',
                    default      => ''
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= $m['statut'] ?></span>
              </td>
              <td>
                <span style="font-size:.7rem;color:<?= $m['visible'] ? 'var(--success)' : 'var(--gray-400)' ?>;">
                  <?= $m['visible'] ? ' Oui' : ' Non' ?>
                </span>
              </td>
              <td>
                <form method="POST" action="../../Controller/traitement_auth.php" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce mémoire ? Cette action est irréversible.');">
                  <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit" name="btn_supprimer_memoire"> Supprimer</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="margin-top:1rem;display:flex;gap:.6rem;">
          <button class="btn btn-primary" type="submit" name="btn_mettre_a_jour_visibilite"> Mettre à jour la visibilité</button>
          <a href="dashboard.php" class="btn btn-ghost"> Annuler</a>
        </div>
      </form>
    </div>
<?php layout_close(); ?>