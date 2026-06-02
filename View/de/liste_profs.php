<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Gestion des professeurs";
$de        = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$profs     = $de->listerProfs();
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
$q         = $_GET['q'] ?? '';
$page      = $_GET['page'] ?? 1;
$perPage   = 20;
if ($q) {
    $profs = array_filter($profs, fn($p) =>
        stripos($p['nom'], $q) !== false ||
        stripos($p['prenom'], $q) !== false ||
        stripos($p['idUtilisateur'], $q) !== false
    );
}
$totalProfs  = count($profs);
$totalPages = ceil($totalProfs / $perPage);
$profs      = array_slice($profs, ($page - 1) * $perPage, $perPage);
?>
<?php
$pageTitle   = "Professeurs";
$pageSection = "Direction des études";
$activeRoute = "professeurs";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <!-- Enregistrer prof -->
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-title"> Enregistrer un professeur</div>
      <form method="POST" action="../../Controller/traitement_auth.php" id="form_prof">
        <input type="hidden" name="idUtilisateur_original" id="idUtilisateur_original_prof">
        <div class="frow3">
          <div class="fg"><label class="fl">Num. Employé *</label><input class="fi" type="text" name="idUtilisateur" id="idUtilisateur_prof" placeholder="PROF001" required></div>
          <div class="fg"><label class="fl">Nom *</label><input class="fi" type="text" name="nom" id="nom_prof" required></div>
          <div class="fg"><label class="fl">Prénom *</label><input class="fi" type="text" name="prenom" id="prenom_prof" required></div>
        </div>
        <div class="frow3">
          <div class="fg"><label class="fl">Email *</label><input class="fi" type="email" name="email" id="email_prof" required></div>
          <div class="fg"><label class="fl">Spécialité *</label><input class="fi" type="text" name="specialite" id="specialite_prof" placeholder="Génie Logiciel" required></div>
          <div class="fg" style="display:flex;align-items:flex-end;gap:.5rem;">
            <button class="btn btn-primary btn-full" type="submit" name="btn_enregistrer_prof" id="btn_enregistrer_prof"> Enregistrer</button>
            <button class="btn btn-danger btn-full" type="submit" name="btn_supprimer_prof" id="btn_supprimer_prof" style="display:none;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce professeur ? Cette action est irréversible.');"> Supprimer</button>
            <button class="btn btn-ghost btn-full" type="button" id="btn_annuler_prof" style="display:none;"> Annuler</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Liste profs -->
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
        <div class="card-title" style="margin-bottom:0;">Liste des professeurs (<?= count($profs) ?>)</div>
        <div style="display:flex;gap:.4rem;">
          <a href="import_profs.php" class="btn btn-emerald btn-sm"> Import CSV</a>
          <form method="GET" style="display:flex;gap:.4rem;">
            <input class="fi" style="width:200px;" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder=" Rechercher...">
            <button class="btn btn-ghost btn-sm" type="submit">OK</button>
          </form>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Num. Employé</th><th>Nom / Prénom</th><th>Spécialité</th></tr>
          </thead>
          <tbody>
          <?php foreach ($profs as $p): ?>
          <tr style="cursor:pointer;" onclick="chargerProf('<?= htmlspecialchars($p['idUtilisateur']) ?>', '<?= htmlspecialchars($p['nom']) ?>', '<?= htmlspecialchars($p['prenom']) ?>', '<?= htmlspecialchars($p['email']) ?>', '<?= htmlspecialchars($p['specialite'] ?? '') ?>')">
            <td style="font-family:monospace;font-size:.72rem;"><?= htmlspecialchars($p['idUtilisateur']) ?></td>
            <td><strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong><br><span style="font-size:.65rem;color:var(--gray-400);"><?= htmlspecialchars($p['email']) ?></span></td>
            <td><?= htmlspecialchars($p['specialite'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($totalPages > 1): ?>
      <div style="display:flex;justify-content:center;align-items:center;gap:.5rem;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--gray-200);">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&q=<?= htmlspecialchars($q) ?>" class="btn btn-ghost btn-sm"> Précédent</a>
        <?php endif; ?>
        <span style="font-size:.75rem;color:var(--gray-600);">Page <?= $page ?> sur <?= $totalPages ?> (<?= $totalProfs ?> professeurs)</span>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&q=<?= htmlspecialchars($q) ?>" class="btn btn-ghost btn-sm">Suivant <?= icon('arrow-right', 'w-4 h-4 inline-block align-text-bottom') ?></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
<?php layout_close(); ?>