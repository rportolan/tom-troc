<?php

class MessageController
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

    /**
     * Retourne l'id conversation entre moi et l'autre user.
     * Si $createIfMissing = true, crée la conversation si absente.
     */
    private function getConversationId(int $meId, int $otherId, bool $createIfMissing): ?int
    {
        if ($otherId <= 0 || $otherId === $meId) return null;

        $stmt = $this->pdo->prepare("
            SELECT id
            FROM conversations
            WHERE (user_one_id = :me AND user_two_id = :other)
               OR (user_one_id = :other AND user_two_id = :me)
            LIMIT 1
        ");
        $stmt->execute([':me' => $meId, ':other' => $otherId]);
        $conv = $stmt->fetch();

        if ($conv) return (int)$conv['id'];
        if (!$createIfMissing) return null;

        $ins = $this->pdo->prepare("
            INSERT INTO conversations (user_one_id, user_two_id)
            VALUES (:me, :other)
        ");
        $ins->execute([':me' => $meId, ':other' => $otherId]);

        return (int)$this->pdo->lastInsertId();
    }

    private function findUserByPseudo(string $pseudo): ?array
    {
        $pseudo = trim($pseudo);
        if ($pseudo === '') return null;

        $stmt = $this->pdo->prepare("SELECT id, pseudo, avatar FROM users WHERE pseudo = :p LIMIT 1");
        $stmt->execute([':p' => $pseudo]);
        $u = $stmt->fetch();

        return $u ?: null;
    }

    public function index()
    {
        $this->requireLogin();

        $title = "Messagerie";
        $meId = (int)$_SESSION['user']['id'];

        // 1) Liste des conversations de l'utilisateur (threads)
        // + dernier message (preview + date)
        $stmt = $this->pdo->prepare("
            SELECT
                c.id AS conversation_id,
                CASE 
                    WHEN c.user_one_id = :me THEN u2.id 
                    ELSE u1.id 
                END AS other_id,
                CASE 
                    WHEN c.user_one_id = :me THEN u2.pseudo 
                    ELSE u1.pseudo 
                END AS other_pseudo,
                CASE 
                    WHEN c.user_one_id = :me THEN u2.avatar 
                    ELSE u1.avatar 
                END AS other_avatar,
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
        $stmt->execute([':me' => $meId]);
        $threads = $stmt->fetchAll();

        // 2) Conversation sélectionnée :
        // - priorité à ?conv=ID
        // - sinon ?to=pseudo (et crée conversation si nécessaire)
        $convId = (int)($_GET['conv'] ?? 0);
        $to = trim($_GET['to'] ?? '');

        $current = null;      // pseudo du contact
        $messages = [];
        $activeConvId = null; // id conv ouverte

        if ($convId > 0) {
            $activeConvId = $convId;
        } elseif ($to !== '') {
            $other = $this->findUserByPseudo($to);
            if ($other) {
                $activeConvId = $this->getConversationId($meId, (int)$other['id'], true);
            }
        } elseif (!empty($threads)) {
            // par défaut : ouvrir la première conversation
            $activeConvId = (int)$threads[0]['conversation_id'];
        }

        // 3) Charger infos contact + messages
        if ($activeConvId) {
            // Vérifie que la conversation appartient bien à l'utilisateur
            $check = $this->pdo->prepare("
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
            $check->execute([':cid' => $activeConvId, ':me' => $meId]);
            $convRow = $check->fetch();

            if ($convRow) {
                $current = $convRow['other_pseudo'];
                $currentAvatar = $convRow['other_avatar'] ? '/images/' . $convRow['other_avatar'] : '/images/test.png';

                $stmt = $this->pdo->prepare("
                    SELECT id, sender_id, content, created_at
                    FROM messages
                    WHERE conversation_id = :cid
                    ORDER BY id ASC
                ");
                $stmt->execute([':cid' => $activeConvId]);
                $rows = $stmt->fetchAll();

                $messages = array_map(function($m) use ($meId) {
                    return [
                        'side' => ((int)$m['sender_id'] === $meId) ? 'right' : 'left',
                        'date' => date('d.m H:i', strtotime($m['created_at'])),
                        'text' => $m['content'],
                    ];
                }, $rows);

                // On expose l'avatar au view (optionnel)
                $currentMeta = [
                    'pseudo' => $current,
                    'avatar' => $currentAvatar,
                ];
            } else {
                $activeConvId = null;
            }
        }

        ob_start();
        require __DIR__ . '/../views/messages.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    public function send()
    {
        $this->requireLogin();

        $meId = (int)$_SESSION['user']['id'];
        $convId = (int)($_POST['conversation_id'] ?? 0);
        $text = trim($_POST['content'] ?? '');

        if ($convId <= 0 || $text === '') {
            header('Location: /?page=messages&error=Message invalide');
            exit;
        }

        // Sécurité : vérifier que conv appartient au user
        $check = $this->pdo->prepare("
            SELECT id
            FROM conversations
            WHERE id = :cid AND (user_one_id = :me OR user_two_id = :me)
            LIMIT 1
        ");
        $check->execute([':cid' => $convId, ':me' => $meId]);

        if (!$check->fetch()) {
            header('Location: /?page=messages&error=Conversation introuvable');
            exit;
        }

        // Insert message
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (conversation_id, sender_id, content)
            VALUES (:cid, :sid, :content)
        ");
        $stmt->execute([
            ':cid' => $convId,
            ':sid' => $meId,
            ':content' => $text,
        ]);

        header('Location: /?page=messages&conv=' . $convId);
        exit;
    }
}
