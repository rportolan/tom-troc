<?php
declare(strict_types=1);

final class AuthController
{
    public function __construct(private UserRepository $users) {}

    public function loginForm(): void
    {
        $title = "Connexion";
        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/login.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function registerForm(): void
    {
        $title = "Inscription";
        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/register.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function register(): void
    {
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($pseudo === '' || $email === '' || $password === '') {
            header('Location: /?page=register&error=Veuillez remplir tous les champs'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /?page=register&error=Email invalide'); exit;
        }
        if (strlen($password) < 6) {
            header('Location: /?page=register&error=Mot de passe trop court (6 caractères minimum)'); exit;
        }
        if ($this->users->existsEmailOrPseudo($email, $pseudo)) {
            header('Location: /?page=register&error=Email ou pseudo déjà utilisé'); exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id = $this->users->create($pseudo, $email, $hash);

        $_SESSION['user'] = ['id' => $id, 'pseudo' => $pseudo, 'email' => $email];
        header('Location: /?page=books'); exit;
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            header('Location: /?page=login&error=Veuillez remplir tous les champs'); exit;
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, (string)$user->passwordHash())) {
            header('Location: /?page=login&error=Identifiants incorrects'); exit;
        }

        $_SESSION['user'] = $user->toSessionArray();
        header('Location: /?page=books'); exit;
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        header('Location: /?page=home'); exit;
    }
}
