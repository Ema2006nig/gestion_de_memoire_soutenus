<?php
require_once __DIR__ . '/../config/config.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function current_user(): ?array {
    start_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): array {
    $u = current_user();
    if (!$u) redirect('/index.php?route=login');
    return $u;
}

function require_role(array $roles): array {
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die('Acces refuse.');
    }
    return $u;
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    start_session();
    $t = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        die('Jeton CSRF invalide.');
    }
}

function flash(string $key, ?string $msg = null) {
    start_session();
    if ($msg === null) {
        $m = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    $_SESSION['flash'][$key] = $msg;
}

function send_mail(string $to, string $sujet, string $corps): bool {
    $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    // Fallback : log dans uploads/mail.log si mail() echoue (utile en dev)
    if (!@mail($to, $sujet, $corps, $headers)) {
        @file_put_contents(UPLOAD_DIR . '/mail.log',
            "[" . date('c') . "] TO=$to | $sujet\n$corps\n----\n", FILE_APPEND);
        return false;
    }
    return true;
}

function view(string $tpl, array $data = []): void {
    extract($data);
    $user = current_user();
    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $tpl . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}
