<?php
declare(strict_types=1);

final class MessageRepository
{
    public function __construct(private PDO $pdo) {}

    public function listByConversation(int $convId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, conversation_id, sender_id, content, created_at
            FROM messages
            WHERE conversation_id = :cid
            ORDER BY id ASC
        ");
        $stmt->execute([':cid' => $convId]);
        $rows = $stmt->fetchAll();

        return array_map(fn($r) => Message::fromRow($r), $rows);
    }

    public function create(int $convId, int $senderId, string $content): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (conversation_id, sender_id, content)
            VALUES (:cid, :sid, :content)
        ");
        $stmt->execute([':cid' => $convId, ':sid' => $senderId, ':content' => $content]);
        return (int)$this->pdo->lastInsertId();
    }
}
