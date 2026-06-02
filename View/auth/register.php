<?php
/**
 * Inscription en une étape.
 *
 * L'utilisateur (étudiant, prof, DE, bibliothécaire) doit déjà avoir été
 * enregistré côté Direction. Il utilise l'e-mail fourni par l'administration
 * pour activer son compte et choisir un mot de passe.
 */
session_start();
require_once __DIR__ . '/../../Config/config.php';

if (isset($_SESSION['idCompte'])) {
    header('Location: ../etudiant/dashboard.php');
    exit;
}

require_once __DIR__ . '/_auth_layout.php';
auth_layout_open('Créer mon compte');
?>
<h1>Activer mon compte.</h1>
<p class="lead">Renseignez l'e-mail communiqué par la Direction et choisissez votre mot de passe.</p>

<?php flash_messages(); ?>

<form method="POST" action="<?= BASE_URL ?>/Controller/traitement_auth.php" novalidate>
    <div class="field">
        <label class="label" for="email">Adresse e-mail institutionnelle</label>
        <div class="input-with-icon">
            <span class="ico"><?= icon('mail', 'w-4 h-4') ?></span>
            <input class="input" type="email" id="email" name="email" required autofocus
                   placeholder="prenom.nom@uatm.ga"
                   value="<?= htmlspecialchars($_SESSION['form_register']['email'] ?? '') ?>">
        </div>
        <div class="help">Cette adresse doit avoir été enregistrée par la Direction des Études.</div>
    </div>

    <div class="field-row">
        <div class="field">
            <label class="label" for="motDePasse">Mot de passe</label>
            <div class="input-with-icon">
                <span class="ico"><?= icon('lock', 'w-4 h-4') ?></span>
                <input class="input" type="password" id="motDePasse" name="motDePasse"
                       required minlength="6" placeholder="6 caractères min.">
            </div>
        </div>
        <div class="field">
            <label class="label" for="confirmer">Confirmation</label>
            <div class="input-with-icon">
                <span class="ico"><?= icon('lock', 'w-4 h-4') ?></span>
                <input class="input" type="password" id="confirmer" name="confirmer"
                       required minlength="6" placeholder="Retapez votre mot de passe">
            </div>
        </div>
    </div>

    <button class="btn btn-accent btn-block" type="submit" name="btn_register">
        Créer mon compte <?= icon('arrow-right', 'w-4 h-4') ?>
    </button>
</form>

<div class="switch">
    Vous avez déjà un compte ?
    <a href="login.php">Se connecter</a>
</div>
<?php
unset($_SESSION['form_register']);
auth_layout_close();