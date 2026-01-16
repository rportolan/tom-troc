<?php
declare(strict_types=1);

final class BookRepository
{
    public function __construct(private PDO $pdo) {}

    public function latest(int $limit): array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.user_id, b.title, b.author, b.description, b.image, b.is_available, b.created_at,
                   u.pseudo AS seller
            FROM books b
            JOIN users u ON u.id = b.user_id
            ORDER BY b.id DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchWithSeller(string $q = ''): array
    {
        $sql = "
            SELECT b.id, b.user_id, b.title, b.author, b.description, b.image, b.is_available, b.created_at,
                   u.pseudo AS seller
            FROM books b
            JOIN users u ON u.id = b.user_id
            WHERE 1=1
        ";
        $params = [];

        if ($q !== '') {
            $sql .= " AND (b.title LIKE :q OR b.author LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $sql .= " ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByIdWithOwner(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.user_id, b.title, b.author, b.description, b.image, b.is_available, b.created_at,
                   u.pseudo AS owner_pseudo, u.avatar AS owner_avatar
            FROM books b
            JOIN users u ON u.id = b.user_id
            WHERE b.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findOwnedById(int $id, int $userId): ?Book
    {
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, title, author, description, image, is_available, created_at
            FROM books
            WHERE id = :id AND user_id = :uid
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ? Book::fromRow($row) : null;
    }

    public function listByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, title, author, description, image, is_available, created_at
            FROM books
            WHERE user_id = :uid
            ORDER BY id DESC
        ");
        $stmt->execute([':uid' => $userId]);

        $rows = $stmt->fetchAll();
        return array_map(fn($r) => Book::fromRow($r), $rows);
    }

    public function create(Book $book): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO books (user_id, title, author, description, image, is_available)
            VALUES (:uid, :title, :author, :description, :img, :avail)
        ");
        $stmt->execute([
            ':uid' => $book->userId(),
            ':title' => $book->title(),
            ':author' => $book->author(),
            ':description' => $book->description(),
            ':img' => $book->image(),
            ':avail' => $book->isAvailable(),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(Book $book): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE books
            SET title = :title,
                author = :author,
                description = :description,
                is_available = :avail,
                image = :img
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([
            ':title' => $book->title(),
            ':author' => $book->author(),
            ':description' => $book->description(),
            ':avail' => $book->isAvailable(),
            ':img' => $book->image(),
            ':id' => $book->id(),
            ':uid' => $book->userId(),
        ]);
    }

    public function deleteOwned(int $id, int $userId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT image FROM books WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $del = $this->pdo->prepare("DELETE FROM books WHERE id = :id AND user_id = :uid");
        $del->execute([':id' => $id, ':uid' => $userId]);

        return $row['image'] ?? null;
    }
}
