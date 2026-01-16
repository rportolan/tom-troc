<?php
declare(strict_types=1);

final class AccountController
{
    public function __construct(private UserRepository $users, private BookRepository $books) {}

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

    public function index(): void
    {
        Auth::requireLogin();

        $title = "Mon compte";
        $userId = Auth::userId();

        $user = $this->users->findById($userId);
        if (!$user) {
            header('Location: /?page=logout'); exit;
        }

        $memberSince = $this->memberSinceLabel($user->createdAt());
        $books = array_map(fn(Book $b) => $b->toArrayForViews(), $this->books->listByUserId($userId));

        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        // Pour garder ta vue telle quelle : on expose un array $user comme avant
        $user = [
            'id' => $user->id(),
            'pseudo' => $user->pseudo(),
            'email' => $user->email(),
            'avatar' => $user->avatar(),
            'created_at' => $user->createdAt(),
        ];

        ob_start();
        require __DIR__ . '/../views/account.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function update(): void
    {
        Auth::requireLogin();
        $userId = Auth::userId();

        $email = trim($_POST['email'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $pseudo === '') {
            header('Location: /?page=account&error=Email et pseudo obligatoires'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /?page=account&error=Email invalide'); exit;
        }
        if ($this->users->existsEmailOrPseudoExceptId($email, $pseudo, $userId)) {
            header('Location: /?page=account&error=Email ou pseudo déjà utilisé'); exit;
        }

        $hash = null;
        if ($password !== '') {
            if (strlen($password) < 6) {
                header('Location: /?page=account&error=Mot de passe trop court (6 caractères minimum)'); exit;
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->users->updateAccount($userId, $email, $pseudo, $hash);

        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['pseudo'] = $pseudo;

        header('Location: /?page=account&success=Informations enregistrées'); exit;
    }
}
