<?php
declare(strict_types=1);

final class MessageController
{
    public function __construct(
        private UserRepository $users,
        private ConversationRepository $conversations,
        private MessageRepository $messagesRepo,
    ) {}

    public function index(): void
    {
        Auth::requireLogin();

        $title = "Messagerie";
        $meId = Auth::userId();

        $threads = $this->conversations->listThreadsForUser($meId);

        $convId = (int)($_GET['conv'] ?? 0);
        $to = trim($_GET['to'] ?? '');

        $current = null;
        $messages = [];
        $activeConvId = null;

        if ($convId > 0) {
            $activeConvId = $convId;
        } elseif ($to !== '') {
            $other = $this->users->findByPseudo($to);
            if ($other && $other->id() !== $meId) {
                $existing = $this->conversations->getBetweenUsers($meId, $other->id());
                $activeConvId = $existing ?? $this->conversations->create($meId, $other->id());
            }
        } elseif (!empty($threads)) {
            $activeConvId = (int)$threads[0]['conversation_id'];
        }

        if ($activeConvId) {
            $meta = $this->conversations->getConversationMetaForUser($activeConvId, $meId);
            if ($meta) {
                $current = $meta['other_pseudo'];
                $currentAvatar = $meta['other_avatar'] ? '/images/' . $meta['other_avatar'] : '/images/test.png';

                $entities = $this->messagesRepo->listByConversation($activeConvId);
                $messages = array_map(function(Message $m) use ($meId) {
                    return [
                        'side' => ($m->senderId() === $meId) ? 'right' : 'left',
                        'date' => date('d.m H:i', strtotime((string)$m->createdAt())),
                        'text' => $m->content(),
                    ];
                }, $entities);

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

    public function send(): void
    {
        Auth::requireLogin();

        $meId = Auth::userId();
        $convId = (int)($_POST['conversation_id'] ?? 0);
        $text = trim($_POST['content'] ?? '');

        if ($convId <= 0 || $text === '') {
            header('Location: /?page=messages&error=Message invalide'); exit;
        }

        if (!$this->conversations->userHasConversation($convId, $meId)) {
            header('Location: /?page=messages&error=Conversation introuvable'); exit;
        }

        $this->messagesRepo->create($convId, $meId, $text);
        header('Location: /?page=messages&conv=' . $convId); exit;
    }
}
