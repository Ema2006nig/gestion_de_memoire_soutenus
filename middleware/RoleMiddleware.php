<?php
class RoleMiddleware {
    public static function require($allowedRoles = []) {
        session_start();
        if(!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
        $userRole = $_SESSION['role'] ?? '';
        if(!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            echo "<h2>Accès interdit</h2><p>Vous n'avez pas les permissions nécessaires.</p>";
            exit;
        }
    }
}