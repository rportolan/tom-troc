<section class="container messaging">
  <div class="messaging-grid">
    <aside class="messaging-left">
      <div class="messaging-left__head">
        <h1 class="messaging-left__title">Messagerie</h1>
      </div>

      <div class="thread-list">
        <?php if (empty($threads)): ?>
          <div class="thread" style="pointer-events:none; opacity:.7;">
            <div class="thread__meta">
              <div class="thread__top">
                <div class="thread__name">Aucune conversation</div>
              </div>
              <div class="thread__preview">Commence en cliquant “Écrire un message” sur un profil.</div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($threads as $t): ?>
            <?php
              $active = (!empty($activeConvId) && (int)$t['conversation_id'] === (int)$activeConvId);

              $avatar = !empty($t['other_avatar']) ? '/images/' . $t['other_avatar'] : '/images/test.png';

              $time = '';
              if (!empty($t['last_date'])) {
                $time = date('d.m H:i', strtotime($t['last_date']));
              }

              $preview = trim((string)($t['last_content'] ?? ''));
              if ($preview === '') $preview = 'Aucun message pour le moment...';
              if (mb_strlen($preview) > 45) $preview = mb_substr($preview, 0, 45) . '...';
            ?>

            <a class="thread <?= $active ? 'thread--active' : '' ?>"
               href="/?page=messages&conv=<?= (int)$t['conversation_id'] ?>">
              <img class="thread__avatar" src="<?= htmlspecialchars($avatar) ?>" alt="Avatar">
              <div class="thread__meta">
                <div class="thread__top">
                  <div class="thread__name"><?= htmlspecialchars($t['other_pseudo']) ?></div>
                  <div class="thread__time"><?= htmlspecialchars($time) ?></div>
                </div>
                <div class="thread__preview"><?= htmlspecialchars($preview) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </aside>

    <div class="messaging-right">
      <?php
        $chatName = $current ?? '—';
        $chatAvatar = $currentMeta['avatar'] ?? '/images/test.png';
      ?>

      <div class="chat-head">
        <img class="chat-head__avatar" src="<?= htmlspecialchars($chatAvatar) ?>" alt="Avatar">
        <div class="chat-head__name"><?= htmlspecialchars($chatName) ?></div>
      </div>

      <div class="chat-area">
        <?php if (empty($activeConvId)): ?>
          <div style="opacity:.7; padding:24px;">
            Sélectionne une conversation à gauche, ou clique sur “Écrire un message” depuis un profil.
          </div>
        <?php else: ?>
          <?php if (empty($messages)): ?>
            <div style="opacity:.7; padding:24px;">
              Aucun message pour le moment. Envoie le premier 👇
            </div>
          <?php else: ?>
            <?php foreach ($messages as $m): ?>
              <div class="msg-row msg-row--<?= htmlspecialchars($m['side']) ?>">
                <div class="msg-date"><?= htmlspecialchars($m['date']) ?></div>
                <div class="msg-bubble msg-bubble--<?= htmlspecialchars($m['side']) ?>">
                  <?= nl2br(htmlspecialchars($m['text'])) ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($activeConvId)): ?>
        <form class="chat-input" method="post" action="/?page=message_send">
          <input type="hidden" name="conversation_id" value="<?= (int)$activeConvId ?>">
          <input class="chat-input__field" type="text" name="content" placeholder="Tapez votre message ici" required>
          <button class="btn btn--primary chat-input__btn" type="submit">Envoyer</button>
        </form>
      <?php else: ?>
      <?php endif; ?>
    </div>

  </div>
</section>
