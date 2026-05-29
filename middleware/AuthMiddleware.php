<?php
class AuthMiddleware {
    public static function check() {
        session_start();
        if(!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }
}