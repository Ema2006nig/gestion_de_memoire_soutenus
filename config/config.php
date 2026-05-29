<?php
// Configuration generale (modifier selon votre serveur)
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_memoires');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/gestion_de_memoire_soutenus/public');     // URL publique du dossier public/
define('UPLOAD_DIR', __DIR__ . '/../uploads');

// Mail (SMTP simple via mail() PHP, ou remplacer par PHPMailer si besoin)
define('MAIL_FROM', 'no-reply@univ.local');
define('MAIL_FROM_NAME', 'Gestion Memoires');

// Securite
define('SESSION_NAME', 'gm_sess');
define('CSRF_KEY', 'changez-moi-en-prod');
