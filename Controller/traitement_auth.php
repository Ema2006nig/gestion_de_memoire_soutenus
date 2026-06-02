<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Routeur des actions d'authentification.
 * Toutes les soumissions des formulaires login.php et register.php
 * arrivent ici et sont dispatchées au bon contrôleur.
 *
 * Les actions liées aux mémoires (upload, validation, etc.) sont
 * conservées telles quelles via Controllers.php.
 */

require_once __DIR__ . '/../Config/connexion.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/Controllers.php';

$action = $_GET['action'] ?? '';

// ── AUTHENTIFICATION ─────────────────────────────────────────
if ($action === 'logout')                  { AuthController::logout(); }
if (isset($_POST['btn_register']))         { AuthController::register(); }
if (isset($_POST['btn_login']))            { AuthController::login(); }
if (isset($_POST['btn_changer_mdp']))      { AuthController::changerMotDePasse(); }

// ── MÉMOIRES ─────────────────────────────────────────────────
if (isset($_POST['btn_soumettre']))        { MemoireController::upload(); }
if (isset($_POST['btn_commenter']))        { MemoireController::commenter(); }
if (isset($_POST['idMemoire'], $_POST['action_like'])) { MemoireController::liker(); }
if (isset($_POST['idMemoire'], $_POST['reponse'])
    && !isset($_POST['btn_valider'], $_POST['btn_rejeter'], $_POST['btn_publier'])) {
    MemoireController::repondreInvitation();
}
if ($action === 'gererInvitation' && isset($_GET['id'])) {
    MemoireController::gererInvitation();
    exit;
}

// ── VALIDATION (Professeur / DE) ─────────────────────────────
if (isset($_POST['btn_valider']))          { ValidationController::valider(); }
if (isset($_POST['btn_rejeter']))          { ValidationController::rejeter(); }
if (isset($_POST['btn_publier']))          { ValidationController::publier(); }
if (isset($_POST['btn_activer_delegation']) || isset($_POST['btn_desactiver_delegation'])) {
    ValidationController::gererDelegation();
}

// ── ADMINISTRATION (DE) ─────────────────────────────────────
if (isset($_POST['btn_enregistrer_etu']))  { AdminController::enregistrerEtudiant(); }
if (isset($_POST['btn_modifier_etu']))     { AdminController::modifierEtudiant(); }
if (isset($_POST['btn_supprimer_etu']))    { AdminController::supprimerEtudiant(); }
if (isset($_POST['btn_enregistrer_prof'])) { AdminController::enregistrerProf(); }
if (isset($_POST['btn_modifier_prof']))    { AdminController::modifierProf(); }
if (isset($_POST['btn_supprimer_prof']))   { AdminController::supprimerProf(); }
if (isset($_POST['btn_activer_upload']))   { AdminController::activerUpload(); }
if (isset($_POST['btn_desactiver_upload'])){ AdminController::desactiverUpload(); }

// Si aucune action correspondante, on revient à l'accueil.
header('Location: ../index.php');
exit;
