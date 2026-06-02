<?php
/**
 * Page de connexion.
 * Identifiant : l'adresse e-mail enregistrée par la Direction.
 */
session_start();
require_once __DIR__ . '/../../Config/config.php';

if (isset($_SESSION['idCompte'])) {
    $dashboards = [
        'DE'             => '../de/dashboard.php',
        'PROFESSEUR'     => '../professeur/dashboard.php',
        'BIBLIOTHECAIRE' => '../bibliothecaire/dashboard.php',
        'ETUDIANT'       => '../etudiant/dashboard.php',
    ];
    header('Location: ' . ($dashboards[$_SESSION['role']] ?? '../etudiant/dashboard.php'));
    exit;
}

require_once __DIR__ . '/_auth_layout.php';
auth_layout_open('Connexion');
?>
<h1>Bonjour, content de vous revoir.</h1>
<p class="lead">Connectez-vous avec l'e-mail que la Direction a enregistré pour vous.</p>

<?php flash_messages(); ?>

<form method="POST" action="<?= BASE_URL ?>/Controller/traitement_auth.php" novalidate>
    <div class="field">
        <label class="label" for="email">Adresse e-mail</label>
        <div class="input-with-icon">
            <span class="ico"><?= icon('mail', 'w-4 h-4') ?></span>
            <input class="input" type="email" id="email" name="email" autocomplete="email"
                   required autofocus placeholder="prenom.nom@uatm.ga"
                   value="<?= htmlspecialchars($_SESSION['form_login']['email'] ?? '') ?>">
        </div>
    </div>

    <div class="field">
        <label class="label" for="motDePasse">Mot de passe</label>
        <div class="input-with-icon">
            <span class="ico"><?= icon('lock', 'w-4 h-4') ?></span>
            <input class="input" type="password" id="motDePasse" name="motDePasse"
                   autocomplete="current-password" required placeholder="••••••••">
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit" name="btn_login">
        Se connecter <?= icon('arrow-right', 'w-4 h-4') ?>
    </button>
</form>

<div class="switch">
    Pas encore de compte ?
    <a href="register.php">Créer mon compte</a>
</div>
<?php
unset($_SESSION['form_login']);
auth_layout_close();