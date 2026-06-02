<?php
/**
 * Configuration globale de l'application.
 *
 * Calcule automatiquement BASE_URL et ASSETS_URL en fonction du dossier
 * dans lequel le projet est déposé (htdocs/memoires_app par défaut).
 */

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Cherche le dossier racine du projet à partir du chemin du script courant
    $script   = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $segments = explode('/', trim($script, '/'));

    $root  = '';
    foreach ($segments as $segment) {
        $root .= '/' . $segment;
        if (preg_match('/^memoires/i', $segment)) {
            break;
        }
    }

    if ($root === '' && !empty($segments)) {
        $root = '/' . $segments[0];
    }

    define('BASE_URL',   $protocol . '://' . $host . $root);
    define('ASSETS_URL', BASE_URL . '/assets');
    define('APP_NAME',   'Mémoires Soutenus');
    define('UNIVERSITY', 'UATM GASA Formation');
}
