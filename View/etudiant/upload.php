<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireLogin();

$pdo = getConnexion();

// Vérifier upload autorisé (depuis la base)
$stmt = $pdo->prepare("SELECT peutUploader FROM etudiant WHERE idUtilisateur = ?");
$stmt->execute([$_SESSION['idUtilisateur']]);
$peutUploader = (bool)$stmt->fetchColumn();

if (!$peutUploader) {
    $_SESSION['erreur'] = "Vous n'êtes pas autorisé à soumettre un mémoire.";
    header('Location: dashboard.php'); exit;
}

require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle = "Soumettre mon mémoire";
$profs     = Professeur::listerTous();
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
$annee     = date('Y') . '-' . (date('Y') + 1);

// ── Détecter le contexte ─────────────────────────────────────────────────────

// 1. Mémoire REJETÉ (auteur principal)
$stmt = $pdo->prepare("
    SELECT m.*, p.nom AS nomProf, p.prenom AS prenomProf
    FROM memoire m JOIN utilisateur p ON m.idProf = p.idUtilisateur
    WHERE m.idCompte = ? AND m.statut = 'REJETE' LIMIT 1");
$stmt->execute([$_SESSION['idCompte']]);
$memoireRejete = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Co-auteur avec mémoire REJETE
$memoireRejeteCoAuth = null;
if (!$memoireRejete) {
    $stmt = $pdo->prepare("
        SELECT m.*, p.nom AS nomProf, p.prenom AS prenomProf
        FROM co_auteur ca JOIN memoire m ON ca.idMemoire = m.idMemoire
        JOIN utilisateur p ON m.idProf = p.idUtilisateur
        WHERE ca.idCompte = ? AND ca.statut = 'ACCEPTE' AND m.statut = 'REJETE' LIMIT 1");
    $stmt->execute([$_SESSION['idCompte']]);
    $memoireRejeteCoAuth = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 3. Mémoire BROUILLON en attente de validation binôme
$memoireBrouillonCoAuth = null;
$stmt = $pdo->prepare("
    SELECT m.*, p.nom AS nomProf, p.prenom AS prenomProf,
           u2.nom AS nomAuteurPrincipal, u2.prenom AS prenomAuteurPrincipal
    FROM co_auteur ca JOIN memoire m ON ca.idMemoire = m.idMemoire
    JOIN utilisateur p ON m.idProf = p.idUtilisateur
    JOIN compte c2 ON m.idCompte = c2.idCompte
    JOIN utilisateur u2 ON c2.idUtilisateur = u2.idUtilisateur
    WHERE ca.idCompte = ? AND ca.statut = 'ACCEPTE' AND m.statut = 'BROUILLON' LIMIT 1");
$stmt->execute([$_SESSION['idCompte']]);
$memoireBrouillonCoAuth = $stmt->fetch(PDO::FETCH_ASSOC);

$memoireRejete = $memoireRejete ?? $memoireRejeteCoAuth;
?>
<?php
$pageTitle   = "Déposer mon mémoire";
$pageSection = "Espace étudiant";
$activeRoute = "upload";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<?php if (isset($_SESSION['erreur'])): ?>
    <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['erreur']) ?><?php unset($_SESSION['erreur']); ?></div>
    <?php endif; ?>

    <?php // ── CAS 3 : Binôme - valider le PDF soumis par l'autre ──────────
    if ($memoireBrouillonCoAuth): ?>
    <div class="card" style="max-width:600px;margin:0 auto;">
      <div class="card-title"><i class="fas fa-user-friends" style="color:#7c3aed;"></i> Validation requise — Mémoire en binôme</div>
      <div class="alert alert-warning">
        <?= icon('info', 'w-4 h-4 inline-block align-text-bottom') ?>
        <strong><?= htmlspecialchars($memoireBrouillonCoAuth['prenomAuteurPrincipal'].' '.$memoireBrouillonCoAuth['nomAuteurPrincipal']) ?></strong>
        a soumis un nouveau PDF pour le mémoire ci-dessous. Veuillez valider pour l'envoyer à l'encadreur.
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:.9rem 1rem;margin-bottom:1rem;font-size:.82rem;">
        <div style="font-weight:700;color:var(--navy);margin-bottom:.3rem;"><?= htmlspecialchars($memoireBrouillonCoAuth['theme']) ?></div>
        <div style="color:var(--gray-500);">
          Prof. <?= htmlspecialchars($memoireBrouillonCoAuth['nomProf'].' '.$memoireBrouillonCoAuth['prenomProf']) ?>
          &nbsp;·&nbsp; <?= htmlspecialchars($memoireBrouillonCoAuth['anneeAcademique']) ?>
          &nbsp;·&nbsp; <?= htmlspecialchars($memoireBrouillonCoAuth['centre']) ?>
        </div>
      </div>
      <?php if ($memoireBrouillonCoAuth['fichierPdf']): ?>
      <a href="../../uploads/memoires/<?= htmlspecialchars($memoireBrouillonCoAuth['fichierPdf']) ?>" target="_blank" class="btn btn-ghost btn-sm" style="margin-bottom:.8rem;">
        <?= icon('file-text', 'w-4 h-4 inline-block align-text-bottom') ?> Voir le PDF soumis
      </a>
      <?php endif; ?>
      <form method="POST" action="../../Controller/traitement_auth.php">
        <input type="hidden" name="mode_valider_binome" value="1">
        <input type="hidden" name="idMemoire" value="<?= $memoireBrouillonCoAuth['idMemoire'] ?>">
        <button class="btn btn-success" type="submit" name="btn_soumettre" style="width:100%;justify-content:center;">
          <?= icon('check', 'w-4 h-4 inline-block align-text-bottom') ?> Valider — Transmettre à l'encadreur
        </button>
      </form>
    </div>

    <?php // ── CAS 2 : Correction PDF après rejet ──────────────────────────
    elseif ($memoireRejete): ?>
    <div class="card" style="max-width:600px;margin:0 auto;">
      <div class="card-title"><i class="fas fa-redo" style="color:var(--warning);"></i> Soumettre la version corrigée</div>
      <div class="alert alert-danger" style="font-size:.78rem;">
        <?= icon('x', 'w-4 h-4 inline-block align-text-bottom') ?> Mémoire rejeté — <strong>Motif :</strong> <?= htmlspecialchars($memoireRejete['commentaireJury']) ?>
      </div>
      <div class="alert alert-info" style="font-size:.78rem;">
        <?= icon('info', 'w-4 h-4 inline-block align-text-bottom') ?> Les informations du mémoire sont conservées. Soumettez uniquement le <strong>nouveau fichier PDF corrigé</strong>.
      </div>

      <!-- Récap des infos conservées -->
      <div style="background:#f8fafc;border-radius:8px;padding:.9rem 1rem;margin-bottom:1rem;font-size:.82rem;">
        <div style="font-weight:700;color:var(--navy);margin-bottom:.4rem;"><?= htmlspecialchars($memoireRejete['theme']) ?></div>
        <div style="color:var(--gray-500);line-height:1.8;">
          <?= icon('cap', 'w-4 h-4 inline-block align-text-bottom') ?> Prof. <?= htmlspecialchars($memoireRejete['nomProf'].' '.$memoireRejete['prenomProf']) ?><br>
          <?= icon('calendar', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($memoireRejete['anneeAcademique']) ?>
          &nbsp;·&nbsp; <?= icon('map-pin', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($memoireRejete['centre']) ?>
        </div>
      </div>

      <form method="POST" action="../../Controller/traitement_auth.php" enctype="multipart/form-data">
        <input type="hidden" name="mode_correction" value="1">
        <input type="hidden" name="idMemoire" value="<?= $memoireRejete['idMemoire'] ?>">
        <div class="fg">
          <label class="fl">Nouveau fichier PDF *</label>
          <input class="fi" type="file" name="fichierPdf" accept=".pdf" required>
          <small style="color:var(--gray-400);">PDF uniquement, max 20 Mo</small>
        </div>
        <div style="display:flex;gap:.6rem;">
          <button class="btn btn-primary" type="submit" name="btn_soumettre" style="flex:1;justify-content:center;">
            <?= icon('send', 'w-4 h-4 inline-block align-text-bottom') ?> Soumettre la version corrigée
          </button>
          <a href="dashboard.php" class="btn btn-ghost"></a>
        </div>
      </form>
    </div>

    <?php // ── CAS 1 : Nouvelle soumission ──────────────────────────────────
    else: ?>
    <div class="card" style="max-width:600px;margin:0 auto;">
      <div class="card-title"> Soumettre mon mémoire</div>

      <form method="POST" action="../../Controller/traitement_auth.php" enctype="multipart/form-data">
        <input type="hidden" name="mode_correction" value="0">
        <div class="fg">
          <label class="fl">Thème du mémoire *</label>
          <input class="fi" type="text" name="theme" placeholder="Titre complet du mémoire" required>
        </div>
        <div class="frow">
          <div class="fg">
            <label class="fl">Année académique *</label>
            <input class="fi" type="text" name="anneeAcademique" value="<?= $annee ?>" required>
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
          <label class="fl">Professeur encadreur *</label>
          <select class="fsel" name="idProf" required>
            <option value="">-- Sélectionner votre encadreur --</option>
            <?php foreach ($profs as $p): ?>
            <option value="<?= htmlspecialchars($p['idUtilisateur']) ?>">
              Prof. <?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?>
              <?= !empty($p['specialite']) ? ' — '.$p['specialite'] : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="border:1px dashed var(--gray-300);border-radius:8px;padding:.9rem 1rem;margin-bottom:.8rem;">
          <div style="font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:.5rem;">
            <?= icon('users', 'w-4 h-4 inline-block align-text-bottom') ?> Co-auteur (binôme — optionnel)
          </div>
          <div class="fg" style="margin:0;">
            <label class="fl">Matricule du co-auteur</label>
            <input class="fi" type="text" name="co_auteur_matricule" placeholder="Laisser vide si mémoire individuel">
            <small style="color:var(--gray-400);font-size:.68rem;">
              Votre co-auteur recevra une invitation. Il devra valider les informations avant que le mémoire soit soumis à l'encadreur. Les deux auront les mêmes droits.
            </small>
          </div>
        </div>

        <div class="fg">
          <label class="fl">Fichier PDF *</label>
          <input class="fi" type="file" name="fichierPdf" accept=".pdf" required>
          <small style="color:var(--gray-400);">PDF uniquement, max 20 Mo</small>
        </div>

        <div class="alert alert-info" style="font-size:.78rem;">
          <?= icon('info', 'w-4 h-4 inline-block align-text-bottom') ?> Le mémoire sera examiné par votre encadreur, puis publié par la Direction des Études.
        </div>

        <div style="display:flex;gap:.6rem;">
          <button class="btn btn-primary" type="submit" name="btn_soumettre" style="flex:1;justify-content:center;">
            <?= icon('send', 'w-4 h-4 inline-block align-text-bottom') ?> Soumettre
          </button>
          <a href="dashboard.php" class="btn btn-ghost"> Annuler</a>
        </div>
      </form>
    </div>
    <?php endif; ?>
<?php layout_close(); ?>