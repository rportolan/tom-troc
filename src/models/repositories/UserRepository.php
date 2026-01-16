<?php
declare(strict_types=1);

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT id, pseudo, email, password, avatar, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT id, pseudo, email, password, avatar, created_at FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function findByPseudo(string $pseudo): ?User
    {
        $stmt = $this->pdo->prepare("SELECT id, pseudo, email, password, avatar, created_at FROM users WHERE pseudo = :p LIMIT 1");
        $stmt->execute([':p' => $pseudo]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function existsEmailOrPseudo(string $email, string $pseudo): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email OR pseudo = :pseudo LIMIT 1");
        $stmt->execute([':email' => $email, ':pseudo' => $pseudo]);
        return (bool)$stmt->fetch();
    }

    public function existsEmailOrPseudoExceptId(string $email, string $pseudo, int $id): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM users
            WHERE (email = :email OR pseudo = :pseudo) AND id != :id
            LIMIT 1
        ");
        $stmt->execute([':email' => $email, ':pseudo' => $pseudo, ':id' => $id]);
        return (bool)$stmt->fetch();
    }

    public function create(string $pseudo, string $email, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (pseudo, email, password, avatar, created_at)
            VALUES (:pseudo, :email, :password, NULL, NOW())
        ");
        $stmt->execute([':pseudo' => $pseudo, ':email' => $email, ':password' => $passwordHash]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateAccount(int $id, string $email, string $pseudo, ?string $passwordHash): void
    {
        if ($passwordHash !== null) {
            $stmt = $this->pdo->prepare("UPDATE users SET email = :email, pseudo = :pseudo, password = :pass WHERE id = :id");
            $stmt->execute([':email' => $email, ':pseudo' => $pseudo, ':pass' => $passwordHash, ':id' => $id]);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE users SET email = :email, pseudo = :pseudo WHERE id = :id");
        $stmt->execute([':email' => $email, ':pseudo' => $pseudo, ':id' => $id]);
    }
}
