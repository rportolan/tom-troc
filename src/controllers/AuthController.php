<?php

class AuthController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function loginForm()
    {
        $title = "Connexion";
        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/login.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    public function registerForm()
    {
        $title = "Inscription";
        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/register.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    public function register()
    {
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($pseudo === '' || $email === '' || $password === '') {
            header('Location: /?page=register&error=Veuillez remplir tous les champs');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /?page=register&error=Email invalide');
            exit;
        }

        if (strlen($password) < 6) {
            header('Location: /?page=register&error=Mot de passe trop court (6 caractères minimum)');
            exit;
        }

        // Vérifie pseudo/email uniques
        $check = $this->pdo->prepare("SELECT id FROM users WHERE email = :email OR pseudo = :pseudo LIMIT 1");
        $check->execute([
            ':email' => $email,
            ':pseudo' => $pseudo
        ]);
        if ($check->fetch()) {
            header('Location: /?page=register&error=Email ou pseudo déjà utilisé');
            exit;
        }

        // Insert user
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (pseudo, email, password, avatar, created_at)
            VALUES (:pseudo, :email, :password, NULL, NOW())
        ");
        $stmt->execute([
            ':pseudo' => $pseudo,
            ':email' => $email,
            ':password' => $hash
        ]);

        // Connecte direct
        $_SESSION['user'] = [
            'id' => (int)$this->pdo->lastInsertId(),
            'pseudo' => $pseudo,
            'email' => $email,
        ];

        header('Location: /?page=books');
        exit;
    }

    public function login()
    {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            header('Location: /?page=login&error=Veuillez remplir tous les champs');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT id, pseudo, email, password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            header('Location: /?page=login&error=Identifiants incorrects');
            exit;
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'pseudo' => $user['pseudo'],
            'email' => $user['email'],
        ];

        header('Location: /?page=books');
        exit;
    }

    public function logout()
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        header('Location: /?page=home');
        exit;
    }
}
