<?php
declare(strict_types=1);

final class ProfileController
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

    public function show(): void
    {
        Auth::requireLogin();

        $title = "Profil";
        $userId = (int)($_GET['id'] ?? 0);

        if ($userId <= 0) {
            header('Location: /?page=books&error=Profil introuvable'); exit;
        }

        $u = $this->users->findById($userId);
        if (!$u) {
            header('Location: /?page=books&error=Profil introuvable'); exit;
        }

        $memberSince = $this->memberSinceLabel($u->createdAt());
        $books = array_map(fn(Book $b) => $b->toArrayForViews(), $this->books->listByUserId($userId));

        $user = [
            'id' => $u->id(),
            'pseudo' => $u->pseudo(),
            'avatar' => $u->avatar(),
            'created_at' => $u->createdAt(),
        ];

        ob_start();
        require __DIR__ . '/../views/profile.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }
}
