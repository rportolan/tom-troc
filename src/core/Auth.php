<?php
declare(strict_types=1);

final class Auth
{
    public static function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /?page=login&error=Veuillez vous connecter');
            exit;
        }
    }

    public static function userId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }
}
