<?php

class AccountController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function requireLogin()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /?page=login&error=Veuillez vous connecter');
            exit;
        }
    }

    private function memberSinceLabel(?string $createdAt): string
    {
        if (!$createdAt) return 'Membre depuis -';

        $start = new DateTime($createdAt);
        $now = new DateTime();
        $diff = $start->diff($now);

        if ($diff->y >= 2) return 'Membre depuis ' . $diff->y . ' ans';
        if ($diff->y === 1) return 'Membre depuis 1 an';
        if ($diff->m >= 2) return 'Membre depuis ' . $diff->m . ' mois';
        if ($diff->m === 1) return 'Membre depuis 1 mois';
        return 'Membre depuis moins d’un mois';
    }

    public function index()
    {
        $this->requireLogin();

        $title = "Mon compte";
        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->pdo->prepare("SELECT id, pseudo, email, avatar, created_at FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        $memberSince = $this->memberSinceLabel($user['created_at'] ?? null);

        $stmt = $this->pdo->prepare("
            SELECT id, title, author, description, image, is_available
            FROM books
            WHERE user_id = :uid
            ORDER BY id DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $books = $stmt->fetchAll();

        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/account.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    public function update()
    {
        $this->requireLogin();

        $userId = (int)$_SESSION['user']['id'];

        $email = trim($_POST['email'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $pseudo === '') {
            header('Location: /?page=account&error=Email et pseudo obligatoires');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /?page=account&error=Email invalide');
            exit;
        }

        // Vérifie unicité email/pseudo (sauf moi)
        $stmt = $this->pdo->prepare("
            SELECT id FROM users
            WHERE (email = :email OR pseudo = :pseudo) AND id != :id
            LIMIT 1
        ");
        $stmt->execute([':email' => $email, ':pseudo' => $pseudo, ':id' => $userId]);
        if ($stmt->fetch()) {
            header('Location: /?page=account&error=Email ou pseudo déjà utilisé');
            exit;
        }

        // Update
        if ($password !== '') {
            if (strlen($password) < 6) {
                header('Location: /?page=account&error=Mot de passe trop court (6 caractères minimum)');
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                UPDATE users SET email = :email, pseudo = :pseudo, password = :pass
                WHERE id = :id
            ");
            $stmt->execute([':email' => $email, ':pseudo' => $pseudo, ':pass' => $hash, ':id' => $userId]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE users SET email = :email, pseudo = :pseudo
                WHERE id = :id
            ");
            $stmt->execute([':email' => $email, ':pseudo' => $pseudo, ':id' => $userId]);
        }

        // Met à jour la session
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['pseudo'] = $pseudo;

        header('Location: /?page=account&success=Informations enregistrées');
        exit;
    }
}
