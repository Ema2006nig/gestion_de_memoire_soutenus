<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Gestion des étudiants";
$de        = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$etudiants = $de->listerEtudiants();
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
$q         = $_GET['q'] ?? '';
$filiere   = $_GET['filiere'] ?? '';
$niveau    = $_GET['niveau'] ?? '';
$page      = $_GET['page'] ?? 1;
$perPage   = 20;

// Récupérer les filières et niveaux uniques pour les filtres
$filtres = [
    'filieres' => array_unique(array_column($etudiants, 'filiere')),
    'niveaux' => array_unique(array_column($etudiants, 'niveau'))
];

if ($q) {
    $etudiants = array_filter($etudiants, fn($e) =>
        stripos($e['nom'], $q) !== false ||
        stripos($e['prenom'], $q) !== false ||
        stripos($e['idUtilisateur'], $q) !== false
    );
}

if ($filiere) {
    $etudiants = array_filter($etudiants, fn($e) => $e['filiere'] === $filiere);
}

if ($niveau) {
    $etudiants = array_filter($etudiants, fn($e) => $e['niveau'] === $niveau);
}
$totalEtudiants = count($etudiants);
$totalPages    = ceil($totalEtudiants / $perPage);
$etudiants     = array_slice($etudiants, ($page - 1) * $perPage, $perPage);
?>
<?php
$pageTitle   = "Étudiants";
$pageSection = "Direction des études";
$activeRoute = "etudiants";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <!-- Enregistrer étudiant -->
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-title"> Enregistrer un étudiant</div>
      <form method="POST" action="../../Controller/traitement_auth.php" id="form_etudiant">
        <input type="hidden" name="idUtilisateur_original" id="idUtilisateur_original">
        <div class="frow3">
          <div class="fg"><label class="fl">Matricule GASA *</label><input class="fi" type="text" name="idUtilisateur" id="idUtilisateur" placeholder="2021INFO0234" required></div>
          <div class="fg"><label class="fl">Nom *</label><input class="fi" type="text" name="nom" id="nom" required></div>
          <div class="fg"><label class="fl">Prénom *</label><input class="fi" type="text" name="prenom" id="prenom" required></div>
        </div>
        <div class="frow3">
          <div class="fg"><label class="fl">Email *</label><input class="fi" type="email" name="email" id="email" required></div>
          <div class="fg"><label class="fl">Date de naissance *</label><input class="fi" type="date" name="dateNaissance" id="dateNaissance" required></div>
          <div class="fg"><label class="fl">Filière *</label><input class="fi" type="text" name="filiere" id="filiere" placeholder="ex: Génie Électrique" required></div>
        </div>
        <div class="frow3">
          <div class="fg"><label class="fl">Option</label><input class="fi" type="text" name="option_etu" id="option_etu" placeholder="ex: Système informatique et logiciel"></div>
          <div class="fg"><label class="fl">Niveau *</label><select class="fsel" name="niveau" id="niveau" required><option value="">--</option><option>L1</option><option>L2</option><option>L3</option><option>M1</option><option>M2</option></select></div>
          <div class="fg" style="display:flex;align-items:flex-end;gap:.5rem;">
            <button class="btn btn-primary btn-full" type="submit" name="btn_enregistrer_etu" id="btn_enregistrer_etu"> Enregistrer</button>
            <button class="btn btn-danger btn-full" type="submit" name="btn_supprimer_etu" id="btn_supprimer_etu" style="display:none;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ? Cette action est irréversible.');"> Supprimer</button>
            <button class="btn btn-ghost btn-full" type="button" id="btn_annuler_etu" style="display:none;"> Annuler</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Liste étudiants -->
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
        <div class="card-title" style="margin-bottom:0;">Liste des étudiants (<?= count($etudiants) ?>)</div>
        <div style="display:flex;gap:.4rem;">
          <a href="import_etudiants.php" class="btn btn-emerald btn-sm"> Import CSV</a>
          <form method="GET" style="display:flex;gap:.4rem;">
            <input class="fi" style="width:200px;" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder=" Rechercher...">
            <select class="fsel" name="filiere" style="width:150px;">
              <option value="">Toutes filières</option>
              <?php foreach ($filtres['filieres'] as $f): ?>
              <option value="<?= htmlspecialchars($f) ?>" <?= $filiere === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="fsel" name="niveau" style="width:100px;">
              <option value="">Tous niveaux</option>
              <?php foreach ($filtres['niveaux'] as $n): ?>
              <option value="<?= htmlspecialchars($n) ?>" <?= $niveau === $n ? 'selected' : '' ?>><?= htmlspecialchars($n) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-ghost btn-sm" type="submit">OK</button>
            <a href="liste_etudiants.php" class="btn btn-ghost btn-sm" style="color:var(--danger);"></a>
          </form>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Matricule</th><th>Nom / Prénom</th><th>Filière · Niveau</th>
              <th>Compte</th><th>Upload</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($etudiants as $e): ?>
          <tr style="cursor:pointer;" onclick="chargerEtudiant('<?= htmlspecialchars($e['idUtilisateur']) ?>', '<?= htmlspecialchars($e['nom']) ?>', '<?= htmlspecialchars($e['prenom']) ?>', '<?= htmlspecialchars($e['email']) ?>', '<?= htmlspecialchars($e['dateNaissance'] ?? '') ?>', '<?= htmlspecialchars($e['filiere'] ?? '') ?>', '<?= htmlspecialchars($e['option_etu'] ?? '') ?>', '<?= htmlspecialchars($e['niveau'] ?? '') ?>')">
            <td style="font-family:monospace;font-size:.72rem;"><?= htmlspecialchars($e['idUtilisateur']) ?></td>
            <td><strong><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></strong><br><span style="font-size:.65rem;color:var(--gray-400);"><?= htmlspecialchars($e['email']) ?></span></td>
            <td><?= htmlspecialchars($e['filiere'] ?? '') ?> · <?= htmlspecialchars($e['niveau'] ?? '') ?></td>
            <td><?= $e['idCompte'] ? '<span class="badge b-ok"> Créé</span>' : '<span class="badge" style="background:#f1f5f9;color:#94a3b8;">Non créé</span>' ?></td>
            <td><?= $e['peutUploader'] ? '<span class="badge b-ok"> Actif</span>' : '<span class="badge b-wait"> Inactif</span>' ?></td>
            <td onclick="event.stopPropagation()">
              <?php if (!$e['peutUploader']): ?>
              <form method="POST" action="../../Controller/traitement_auth.php" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir activer l\'upload pour cet étudiant ?');">
                <input type="hidden" name="idUtilisateur" value="<?= $e['idUtilisateur'] ?>">
                <button class="btn btn-emerald btn-sm" type="submit" name="btn_activer_upload" title="Activer après soutenance"> Activer</button>
              </form>
              <?php else: ?>
              <form method="POST" action="../../Controller/traitement_auth.php" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver l\'upload pour cet étudiant ?');">
                <input type="hidden" name="idUtilisateur" value="<?= $e['idUtilisateur'] ?>">
                <button class="btn btn-danger btn-sm" type="submit" name="btn_desactiver_upload" title="Désactiver l'upload"> Désactiver</button>
              </form>
              <?php endif; ?>
            </td>
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
        <span style="font-size:.75rem;color:var(--gray-600);">Page <?= $page ?> sur <?= $totalPages ?> (<?= $totalEtudiants ?> étudiants)</span>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&q=<?= htmlspecialchars($q) ?>" class="btn btn-ghost btn-sm">Suivant <?= icon('arrow-right', 'w-4 h-4 inline-block align-text-bottom') ?></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
<?php layout_close(); ?>