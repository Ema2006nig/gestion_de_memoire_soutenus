<?php
/**
 * Connexion à la base de données MySQL (PDO singleton).
 *
 * Adapter ces 4 constantes selon votre environnement local.
 * Sur XAMPP / WAMP, les valeurs par défaut conviennent.
 */
const DB_HOST    = 'localhost';
const DB_NAME    = 'memoires_soutenus';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

/**
 * Retourne une instance PDO unique (pattern singleton).
 * Une seule connexion est ouverte par requête HTTP.
 */
function getConnexion(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // En production, logguer l'erreur au lieu de l'afficher
        http_response_code(500);
        die('Connexion à la base impossible. Vérifiez Config/connexion.php.');
    }

    return $pdo;
}
