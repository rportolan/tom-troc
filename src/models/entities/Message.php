<?php
declare(strict_types=1);

final class Message
{
    public function __construct(
        private int $id,
        private int $conversationId,
        private int $senderId,
        private string $content,
        private ?string $createdAt = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['conversation_id'],
            (int)$row['sender_id'],
            (string)$row['content'],
            $row['created_at'] ?? null
        );
    }

    public function senderId(): int { return $this->senderId; }
    public function content(): string { return $this->content; }
    public function createdAt(): ?string { return $this->createdAt; }
}
