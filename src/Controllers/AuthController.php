<?php
require_once __DIR__ . '/../core/Database.php'; // à adapter selon ton core (si besoin)
class AuthController {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function login() {
        session_start();
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée";
            header('Location: /login.php');
            exit;
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['fullname'];
            // Redirection propre selon rôle
            $dashboard = match($user['role']) {
                'etudiant' => '/dashboard-etudiant.php',
                'professeur' => '/dashboard-professeur.php',
                'direction' => '/dashboard-direction.php',
                'admin' => '/dashboard-admin.php',
                default => '/login.php'
            };
            header("Location: $dashboard");
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header('Location: /login.php');
        }
        exit;
    }

    public function register() {
        session_start();
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register.php');
            exit;
        }
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'etudiant';

        if(empty($fullname) || empty($email) || empty($password)) {
            $_SESSION['register_error'] = "Tous les champs sont requis";
            header('Location: /register.php');
            exit;
        }
        // Vérifier email existant
        $check = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute(['email' => $email]);
        if($check->fetch()) {
            $_SESSION['register_error'] = "Cet email est déjà utilisé";
            header('Location: /register.php');
            exit;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'fullname' => $fullname,
            'email' => $email,
            'password' => $hashed,
            'role' => $role
        ]);
        $_SESSION['success'] = "Compte créé, vous pouvez vous connecter";
        header('Location: /login.php');
        exit;
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login.php');
        exit;
    }
}