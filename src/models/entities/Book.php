<?php
declare(strict_types=1);

final class Book
{
    public function __construct(
        private int $id,
        private int $userId,
        private string $title,
        private string $author,
        private string $description,
        private ?string $image,
        private int $isAvailable,
        private ?string $createdAt = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['user_id'],
            (string)$row['title'],
            (string)$row['author'],
            (string)($row['description'] ?? ''),
            $row['image'] ?? null,
            (int)($row['is_available'] ?? 1),
            $row['created_at'] ?? null
        );
    }

    public function id(): int { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function title(): string { return $this->title; }
    public function author(): string { return $this->author; }
    public function description(): string { return $this->description; }
    public function image(): ?string { return $this->image; }
    public function isAvailable(): int { return $this->isAvailable; }

    public function withImage(?string $image): self
    {
        return new self(
            $this->id, $this->userId, $this->title, $this->author, $this->description,
            $image, $this->isAvailable, $this->createdAt
        );
    }

    public function toArrayForViews(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'image' => $this->image ?: 'test.png',
            'is_available' => $this->isAvailable,
            'user_id' => $this->userId,
        ];
    }
}
