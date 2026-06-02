<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('PROFESSEUR');
require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Notification.php';

$pageTitle = "Validation des mémoires";
$prof      = Professeur::trouverParId($_SESSION['idUtilisateur']);
$enAttente = $prof ? $prof->getMemoiresAValider() : [];
$nbNotifs  = Notification::compterNonLus($_SESSION['idCompte']);
?>
<?php
$pageTitle   = "Mémoires à valider";
$pageSection = "Espace enseignant";
$activeRoute = "valider";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>
<div class="alert alert-info" style="font-size:.78rem;"> Après votre validation, le mémoire est transmis à la <strong>Direction des Études</strong> pour publication finale.</div>
    <?php if (isset($_SESSION['succes'])): ?>
    <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['succes']) ?><?php unset($_SESSION['succes']); ?></div>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h2 style="font-size:1rem;font-weight:700;color:var(--navy);">Mémoires à examiner</h2>
      <span class="badge b-wait"><?= count($enAttente) ?> en attente</span>
    </div>

    <?php if (empty($enAttente)): ?>
    <div class="card" style="text-align:center;padding:2.5rem;">
      <div style="font-size:2.5rem;margin-bottom:.6rem;color:var(--success);"></div>
      <div style="font-size:.85rem;font-weight:600;color:var(--success);">Aucun mémoire en attente</div>
      <div style="font-size:.75rem;color:var(--gray-500);margin-top:.3rem;">Tous les mémoires ont été traités.</div>
    </div>
    <?php else: ?>
    <?php foreach ($enAttente as $m): ?>
    <div style="border:1px solid var(--warning);background:#fffbeb;border-radius:var(--radius);padding:1rem;margin-bottom:.8rem;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
      <!-- Info mémoire -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem;">
        <div style="flex:1;">
          <div style="font-size:.85rem;font-weight:700;color:var(--gray-800);margin-bottom:.2rem;"><?= htmlspecialchars($m['theme']) ?></div>
          <div style="font-size:.7rem;color:var(--gray-500);">
            <?= icon('user', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($m['prenomEtudiant'] . ' ' . $m['nomEtudiant']) ?> &nbsp;·&nbsp;
            <?= icon('calendar', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($m['anneeAcademique']) ?> &nbsp;·&nbsp;
            <?= icon('map-pin', 'w-4 h-4 inline-block align-text-bottom') ?> <?= htmlspecialchars($m['centre']) ?>
          </div>
        </div>
        <div style="display:flex;gap:.4rem;">
          <?php if ($m['fichierPdf']): ?>
          <button type="button" onclick="window.open('../../uploads/memoires/<?= htmlspecialchars($m['fichierPdf']) ?>', '_blank')" class="btn btn-primary btn-sm"> Voir PDF</button>
          <?php endif; ?>
          <a href="../shared/detail_memoire.php?id=<?= $m['idMemoire'] ?>" class="btn btn-ghost btn-sm"> Consulter</a>
        </div>
      </div>

      <!-- Formulaire valider -->
      <form method="POST" action="../../Controller/traitement_auth.php" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir valider ce mémoire ? Cette action est irréversible.');">
        <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
        <button class="btn btn-success btn-sm" type="submit" name="btn_valider" style="margin-right:.4rem;"> Valider le mémoire</button>
      </form>

      <!-- Formulaire rejeter avec commentaire -->
      <details style="margin-top:.6rem;">
        <summary style="font-size:.73rem;font-weight:600;color:var(--danger);cursor:pointer;list-style:none;"> Rejeter avec commentaire...</summary>
        <form method="POST" action="../../Controller/traitement_auth.php" style="margin-top:.6rem;" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter ce mémoire ? Cette action est irréversible.');">
          <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
          <div class="fg">
            <label class="fl">Commentaire pour l'étudiant (obligatoire)</label>
            <textarea class="fi" name="commentaireJury" rows="3" placeholder="Indiquez les corrections à apporter..." required style="resize:vertical;"></textarea>
          </div>
          <button class="btn btn-danger btn-sm" type="submit" name="btn_rejeter"> Confirmer le rejet</button>
        </form>
      </details>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
<?php layout_close(); ?>