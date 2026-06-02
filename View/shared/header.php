<?php
/**
 * Compatibilité ascendante : les anciennes vues incluaient shared/header.php
 * puis appelaient renderSidebar(). Le nouveau layout (layout.php) centralise
 * tout. On expose ici un mini-shim qui :
 *   - démarre la session,
 *   - ouvre le layout commun,
 *   - fournit un renderSidebar() vide (l'ancien appel devient un no-op).
 *
 * Les nouvelles vues doivent inclure directement layout.php.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/layout.php';

// Si une variable $activeRoute n'a pas été fixée par la vue, on essaie de
// la deviner à partir du nom du script appelant.
if (!isset($activeRoute)) {
    $base = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
    $map  = [
        'dashboard'         => 'dashboard',
        'liste_memoires'    => 'memoires',
        'notifications'     => 'notifications',
        'liste_etudiants'   => 'etudiants',
        'liste_profs'       => 'professeurs',
        'valider_memoires'  => 'valider',
        'validation'        => 'valider',
        'gestion_visibilite'=> 'visibilite',
        'upload_ancien'     => 'imports',
        'upload_ancien_csv' => 'imports',
        'import_etudiants'  => 'imports',
        'import_profs'      => 'imports',
        'activation_upload' => 'imports',
        'upload'            => 'upload',
        'profil'            => 'profil',
    ];
    $activeRoute = $map[$base] ?? '';
}

layout_open();

function renderSidebar(...$args): void { /* no-op : la sidebar est rendue par layout_open() */ }

// Un buffer de fin est posé pour appeler layout_close() à la destruction.
register_shutdown_function(function () {
    if (function_exists('layout_close')) {
        // Si la page a déjà appelé layout_close, ne rien faire.
        // On utilise un drapeau global pour éviter double rendu.
        if (empty($GLOBALS['__layout_closed'])) {
            $GLOBALS['__layout_closed'] = true;
            layout_close();
        }
    }
});