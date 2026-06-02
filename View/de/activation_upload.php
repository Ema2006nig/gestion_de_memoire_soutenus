<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Activation/Désactivation en masse";
$de        = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$etudiants = $de->listerEtudiants();
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Activation du dépôt";
$pageSection = "Direction des études";
$activeRoute = "imports";
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
      <div class="card-title"> Activation/Désactivation en masse</div>
      <p style="font-size:.78rem;color:var(--gray-500);margin-bottom:1rem;">
        Sélectionnez les étudiants pour activer ou désactiver leur droit d'upload en une seule opération.
      </p>

      <!-- Onglets -->
      <div style="display:flex;gap:.5rem;margin-bottom:1rem;border-bottom:1px solid var(--gray-200);padding-bottom:.5rem;">
        <button type="button" onclick="showTab('selection')" id="tab-selection" style="background:var(--navy);color:#fff;border:none;padding:.5rem 1rem;border-radius:5px;cursor:pointer;font-size:.75rem;">Sélection manuelle</button>
        <button type="button" onclick="showTab('csv')" id="tab-csv" style="background:transparent;color:var(--gray-600);border:none;padding:.5rem 1rem;border-radius:5px;cursor:pointer;font-size:.75rem;">Import CSV</button>
      </div>

      <!-- Tab Sélection manuelle -->
      <div id="content-selection">
        <form method="POST" action="../../Controller/traitement_auth.php">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="width:50px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                <th>Matricule</th>
                <th>Nom / Prénom</th>
                <th>Filière · Niveau</th>
                <th>Upload actuel</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($etudiants as $e): ?>
            <tr>
              <td><input type="checkbox" name="etudiants_selectionnes[]" value="<?= $e['idUtilisateur'] ?>"></td>
              <td style="font-family:monospace;font-size:.72rem;"><?= htmlspecialchars($e['idUtilisateur']) ?></td>
              <td><strong><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></strong><br><span style="font-size:.65rem;color:var(--gray-400);"><?= htmlspecialchars($e['email']) ?></span></td>
              <td><?= htmlspecialchars($e['filiere'] ?? '') ?> · <?= htmlspecialchars($e['niveau'] ?? '') ?></td>
              <td><?= $e['peutUploader'] ? '<span class="badge b-ok"> Actif</span>' : '<span class="badge b-wait"> Inactif</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="margin-top:1rem;display:flex;gap:.6rem;align-items:center;">
          <button class="btn btn-emerald" type="submit" name="btn_activer_upload_masse" onclick="return confirm('Êtes-vous sûr de vouloir activer l\'upload pour les étudiants sélectionnés ?');"> Activer l'upload</button>
          <button class="btn btn-danger" type="submit" name="btn_desactiver_upload_masse" onclick="return confirm('Êtes-vous sûr de vouloir désactiver l\'upload pour les étudiants sélectionnés ?');"> Désactiver l'upload</button>
          <a href="dashboard.php" class="btn btn-ghost">Annuler</a>
        </div>
      </form>
      </div>

      <!-- Tab Import CSV -->
      <div id="content-csv" style="display:none;">
        <form method="POST" action="../../Controller/traitement_auth.php" enctype="multipart/form-data">
          <div class="fg">
            <label class="fl">Fichier CSV des étudiants à activer *</label>
            <input class="fi" type="file" name="fichier_csv" accept=".csv" required>
            <small style="color:var(--gray-400);">Le fichier CSV doit contenir une colonne avec les matricules des étudiants.</small>
          </div>
          <div class="fg">
            <label class="fl">Format du fichier CSV</label>
            <div style="background:#f9f9f9;padding:.8rem;border-radius:5px;font-size:.7rem;color:var(--gray-600);">
              <p style="margin:0 0 .5rem 0;"><strong>Exemple de format :</strong></p>
              <code style="background:#fff;padding:.3rem .5rem;border-radius:3px;display:inline-block;">matricule</code><br>
              <code style="background:#fff;padding:.3rem .5rem;border-radius:3px;display:inline-block;margin-top:.3rem;">ETU001</code><br>
              <code style="background:#fff;padding:.3rem .5rem;border-radius:3px;display:inline-block;margin-top:.3rem;">ETU002</code>
            </div>
          </div>
          <div style="margin-top:1rem;display:flex;gap:.6rem;">
            <button class="btn btn-emerald" type="submit" name="btn_activer_upload_csv" onclick="return confirm('Êtes-vous sûr de vouloir activer l\'upload pour les étudiants du fichier CSV ?');"> Importer et activer</button>
            <a href="dashboard.php" class="btn btn-ghost">Annuler</a>
          </div>
        </form>
      </div>
    </div>
<?php layout_close(); ?>