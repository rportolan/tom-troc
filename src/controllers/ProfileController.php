<?php

class ProfileController
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

    public function show()
    {
        $this->requireLogin();

        $title = "Profil";
        $userId = (int)($_GET['id'] ?? 0);

        if ($userId <= 0) {
            header('Location: /?page=books&error=Profil introuvable');
            exit;
        }

        // Récupère user
        $stmt = $this->pdo->prepare("SELECT id, pseudo, avatar, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            header('Location: /?page=books&error=Profil introuvable');
            exit;
        }

        // Livres du user (tous, même non dispo — tu peux filtrer si tu veux)
        $stmt = $this->pdo->prepare("
            SELECT id, title, author, description, image, is_available
            FROM books
            WHERE user_id = :uid
            ORDER BY id DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $books = $stmt->fetchAll();

        $memberSince = $this->memberSinceLabel($user['created_at'] ?? null);

        ob_start();
        require __DIR__ . '/../views/profile.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }
}
