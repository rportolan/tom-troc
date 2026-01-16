<?php
declare(strict_types=1);

final class ConversationRepository
{
    public function __construct(private PDO $pdo) {}

    public function getBetweenUsers(int $meId, int $otherId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM conversations
            WHERE (user_one_id = :me AND user_two_id = :other)
               OR (user_one_id = :other AND user_two_id = :me)
            LIMIT 1
        ");
        $stmt->execute([':me' => $meId, ':other' => $otherId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    public function create(int $meId, int $otherId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO conversations (user_one_id, user_two_id)
            VALUES (:me, :other)
        ");
        $stmt->execute([':me' => $meId, ':other' => $otherId]);
        return (int)$this->pdo->lastInsertId();
    }

    public function userHasConversation(int $convId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM conversations
            WHERE id = :cid AND (user_one_id = :me OR user_two_id = :me)
            LIMIT 1
        ");
        $stmt->execute([':cid' => $convId, ':me' => $userId]);
        return (bool)$stmt->fetch();
    }

    public function listThreadsForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                c.id AS conversation_id,
                CASE WHEN c.user_one_id = :me THEN u2.id ELSE u1.id END AS other_id,
                CASE WHEN c.user_one_id = :me THEN u2.pseudo ELSE u1.pseudo END AS other_pseudo,
                CASE WHEN c.user_one_id = :me THEN u2.avatar ELSE u1.avatar END AS other_avatar,
                lm.content AS last_content,
                lm.created_at AS last_date
            FROM conversations c
            JOIN users u1 ON u1.id = c.user_one_id
            JOIN users u2 ON u2.id = c.user_two_id
            LEFT JOIN messages lm ON lm.id = (
                SELECT m2.id
                FROM messages m2
                WHERE m2.conversation_id = c.id
                ORDER BY m2.id DESC
                LIMIT 1
            )
            WHERE c.user_one_id = :me OR c.user_two_id = :me
            ORDER BY lm.id DESC, c.id DESC
        ");
        $stmt->execute([':me' => $userId]);
        return $stmt->fetchAll();
    }

    public function getConversationMetaForUser(int $convId, int $meId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.user_one_id, c.user_two_id,
                   CASE WHEN c.user_one_id = :me THEN u2.pseudo ELSE u1.pseudo END AS other_pseudo,
                   CASE WHEN c.user_one_id = :me THEN u2.avatar ELSE u1.avatar END AS other_avatar
            FROM conversations c
            JOIN users u1 ON u1.id = c.user_one_id
            JOIN users u2 ON u2.id = c.user_two_id
            WHERE c.id = :cid
              AND (c.user_one_id = :me OR c.user_two_id = :me)
            LIMIT 1
        ");
        $stmt->execute([':cid' => $convId, ':me' => $meId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
