<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle = "Importer un ancien mémoire";
$de        = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$profs     = Professeur::listerTous();
$etudiants = $de->listerEtudiants();
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);

// Filières et options selon UATM GASA
$filieres = [
    'Génie Électrique' => ['SI' => 'SI — Système Industriel', 'SIL' => 'SIL — Système Informatique et Logiciel', 'AGRO' => 'AGRO — Agronomie'],
    'Sciences de Gestion' => ['COMPTA' => 'Comptabilité', 'FINANCE' => 'Finance', 'MARKETING' => 'Marketing', 'RH' => 'Ressources Humaines'],
    'Droit' => ['DA' => 'Droit des Affaires', 'DC' => 'Droit Civil', 'DP' => 'Droit Pénal', 'DPU' => 'Droit Public'],
    'Economie' => ['MACRO' => 'Macroéconomie', 'MICRO' => 'Microéconomie', 'ECONO' => 'Econométrie'],
];
?>
<?php
$pageTitle   = "Importer un mémoire archivé";
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

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h2 style="font-size:.95rem;font-weight:700;color:var(--navy);"> Importer un ancien mémoire</h2>
      <a href="dashboard.php" class="btn btn-ghost btn-sm"> Retour</a>
    </div>

    <div class="card" style="max-width:680px;margin:0 auto;">
      <div class="alert alert-info" style="font-size:.78rem;"> Le mémoire sera directement publié (statut VALIDE) sans passer par le circuit de validation.</div>

      <form method="POST" action="../../Controller/traitement_auth.php" enctype="multipart/form-data">

        <!-- AUTEUR 1 -->
        <div style="font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:.5rem;padding-bottom:.3rem;border-bottom:2px solid var(--navy);">
          <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?> Auteur principal
        </div>
        <div class="fg">
          <label class="fl">Étudiant *</label>
          <select class="fsel" name="idUtilisateur" id="selEtu1" required>
            <option value="">-- Sélectionner l'étudiant --</option>
            <?php foreach ($etudiants as $e): ?>
            <option value="<?= htmlspecialchars($e['idUtilisateur']) ?>"
                    data-filiere="<?= htmlspecialchars($e['filiere'] ?? '') ?>"
                    data-option="<?= htmlspecialchars($e['option_etu'] ?? '') ?>">
              <?= htmlspecialchars($e['prenom'].' '.$e['nom']) ?> (<?= htmlspecialchars($e['idUtilisateur']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- AUTEUR 2 (binôme optionnel) -->
        <div style="font-size:.78rem;font-weight:700;color:var(--navy);margin:.8rem 0 .5rem;padding-bottom:.3rem;border-bottom:2px solid #e2e8f0;">
          <?= icon('users', 'w-4 h-4 inline-block align-text-bottom') ?> Co-auteur (optionnel — en cas de binôme)
        </div>
        <div class="fg">
          <label class="fl">Deuxième étudiant</label>
          <select class="fsel" name="idUtilisateur2">
            <option value="">-- Aucun (mémoire individuel) --</option>
            <?php foreach ($etudiants as $e): ?>
            <option value="<?= htmlspecialchars($e['idUtilisateur']) ?>">
              <?= htmlspecialchars($e['prenom'].' '.$e['nom']) ?> (<?= htmlspecialchars($e['idUtilisateur']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- ENCADREUR -->
        <div style="font-size:.78rem;font-weight:700;color:var(--navy);margin:.8rem 0 .5rem;padding-bottom:.3rem;border-bottom:2px solid #e2e8f0;">
          <?= icon('cap', 'w-4 h-4 inline-block align-text-bottom') ?> Encadreur & Informations
        </div>
        <div class="fg">
          <label class="fl">Professeur encadreur *</label>
          <select class="fsel" name="idProf" required>
            <option value="">-- Sélectionner le professeur --</option>
            <?php foreach ($profs as $p): ?>
            <option value="<?= htmlspecialchars($p['idUtilisateur']) ?>">
              Prof. <?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?>
              <?= !empty($p['specialite']) ? '— '.$p['specialite'] : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="fg">
          <label class="fl">Thème du mémoire *</label>
          <input class="fi" type="text" name="theme" placeholder="Titre complet du mémoire" required>
        </div>

        <!-- FILIÈRE & OPTION -->
        <div class="frow">
          <div class="fg">
            <label class="fl">Filière *</label>
            <select class="fsel" name="filiere" id="selFiliere" required>
              <option value="">-- Filière --</option>
              <?php foreach (array_keys($filieres) as $f): ?>
              <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg">
            <label class="fl">Option / Spécialité *</label>
            <select class="fsel" name="option_mem" id="selOption" required>
              <option value="">-- Choisir la filière d'abord --</option>
            </select>
          </div>
        </div>

        <!-- ANNÉE & CENTRE -->
        <div class="frow">
          <div class="fg">
            <label class="fl">Année académique *</label>
            <input class="fi" type="text" name="anneeAcademique" placeholder="ex: 2018-2019" required>
          </div>
          <div class="fg">
            <label class="fl">Centre de soutenance *</label>
            <select class="fsel" name="centre" required>
              <option value="">-- Centre --</option>
              <option>AKPAKPA</option><option>AGLA</option><option>PORTO-NOVO</option>
              <option>GBEGAMEY</option><option>CALAVI</option>
            </select>
          </div>
        </div>

        <div class="fg">
          <label class="fl">Fichier PDF (optionnel)</label>
          <input class="fi" type="file" name="fichier_pdf" accept=".pdf">
          <small style="color:var(--gray-400);">Format PDF uniquement, max 20 Mo</small>
        </div>

        <button class="btn btn-primary" type="submit" name="btn_upload_ancien" style="width:100%;justify-content:center;margin-top:.5rem;">
          <?= icon('circle', 'w-4 h-4 inline-block align-text-bottom') ?> Enregistrer et publier
        </button>
      </form>
    </div>
  </div>
</div>
<script>
const optData = <?= json_encode($filieres, JSON_UNESCAPED_UNICODE) ?>;
const selF    = document.getElementById('selFiliere');
const selO    = document.getElementById('selOption');

selF.addEventListener('change', function() {
    const opts = optData[this.value] || {};
    selO.innerHTML = '<option value="">-- Option --</option>';
    Object.entries(opts).forEach(([k,v]) => {
        const el = document.createElement('option');
        el.value = k; el.textContent = v; selO.appendChild(el);
    });
});

// Auto-remplir filière/option depuis l'étudiant sélectionné
document.getElementById('selEtu1').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const f   = opt.getAttribute('data-filiere');
    const o   = opt.getAttribute('data-option');
    if (f && optData[f]) {
        selF.value = f; selF.dispatchEvent(new Event('change'));
        setTimeout(() => {
            // Trouver option dont le label contient la valeur stockée
            Array.from(selO.options).forEach(op => {
                if (op.textContent.toLowerCase().includes(o.toLowerCase().split(' ')[0])) selO.value = op.value;
            });
        }, 80);
    }
});
</script>
<?php layout_close(); ?>