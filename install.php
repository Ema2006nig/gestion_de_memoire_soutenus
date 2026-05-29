<?php
// Script d'installation : cree la base et les comptes de test.
// A executer UNE FOIS depuis le navigateur : http://localhost/gestion_de_memoire_soutenus/install.php
require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);

    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    // On retire les lignes CREATE DATABASE / USE / INSERT (on reinsere proprement)
    $sql = preg_replace('/CREATE DATABASE.*?;\s*/is', '', $sql);
    $sql = preg_replace('/USE .*?;\s*/is', '', $sql);
    $sql = preg_replace('/INSERT INTO utilisateurs.*?;\s*/is', '', $sql);
    $pdo->exec($sql);

    $hash = password_hash('Passw0rd!', PASSWORD_DEFAULT);
    $st = $pdo->prepare('INSERT IGNORE INTO utilisateurs (nom,email,mot_de_passe,role,matricule,grade) VALUES (?,?,?,?,?,?)');
    $users = [
        ['Admin Technique',  'admin@univ.local',         $hash, 'service_technique', null, null],
        ['Direction Etudes', 'direction@univ.local',     $hash, 'direction',         null, null],
        ['Prof Dupont',      'prof@univ.local',          $hash, 'professeur',        null, 'Maitre de conferences'],
        ['Etudiant Test',    'etudiant@univ.local',      $hash, 'etudiant',          'MAT2025', null],
        ['Commentateur',     'commentateur@univ.local',  $hash, 'commentateur',      null, null],
    ];
    foreach ($users as $u) $st->execute($u);

    @mkdir(__DIR__ . '/uploads', 0775, true);

    echo "<h2>Installation reussie.</h2>";
    echo "<p>Comptes crees (mot de passe : <code>Passw0rd!</code>) :</p><ul>";
    foreach ($users as $u) echo "<li>" . htmlspecialchars($u[1]) . " — " . htmlspecialchars($u[3]) . "</li>";
    echo "</ul><p><b>Supprimez ce fichier install.php apres usage.</b></p>";
echo '<p><a href="'.BASE_URL.'/index.php?route=login">Aller a la connexion</a></p>';} catch (Throwable $e) {
    echo "<h2>Erreur d'installation</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
