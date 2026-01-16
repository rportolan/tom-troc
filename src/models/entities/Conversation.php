<?php
declare(strict_types=1);

final class Conversation
{
    public function __construct(
        private int $id,
        private int $userOneId,
        private int $userTwoId,
        private ?string $createdAt = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['user_one_id'],
            (int)$row['user_two_id'],
            $row['created_at'] ?? null
        );
    }

    public function id(): int { return $this->id; }
}
