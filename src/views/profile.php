<section class="container account-page">
  <h1 class="account-title"></h1>

  <?php
    $avatar = !empty($user['avatar']) ? '/images/' . $user['avatar'] : '/images/test.png';
    $isMe = !empty($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$user['id'];
  ?>

  <div class="account-top">
    <!-- Carte profil (gauche) -->
    <div class="card account-profile">
      <div class="account-profile__avatarWrap">
        <img class="account-profile__avatar" src="<?= htmlspecialchars($avatar) ?>" alt="avatar">
      </div>

      <div class="account-profile__sep"></div>

      <div class="account-profile__name"><?= htmlspecialchars($user['pseudo'] ?? 'Utilisateur') ?></div>
      <div class="account-profile__since"><?= htmlspecialchars($memberSince ?? 'Membre depuis -') ?></div>

      <div class="account-profile__lib">
        <div class="account-profile__libTitle">BIBLIOTHÈQUE</div>
        <div class="account-profile__libCount">📚 <?= count($books) ?> livres</div>
      </div>

      <?php if (!$isMe): ?>
        <a class="btn-outline" href="/?page=messages&to=<?= urlencode($user['pseudo']) ?>" style="margin-top:18px; display:inline-flex; justify-content:center;">
          Écrire un message
        </a>
      <?php else: ?>
        <div class="hint" style="margin-top:18px;">C’est votre profil.</div>
      <?php endif; ?>
    </div>

    <!-- Table livres (droite) -->
    <div class="card account-tableWrap" style="flex:1;">
      <table class="account-table">
        <thead>
          <tr>
            <th>PHOTO</th>
            <th>TITRE</th>
            <th>AUTEUR</th>
            <th>DESCRIPTION</th>
            <th>DISPONIBILITÉ</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($books)): ?>
            <tr>
            <td colspan="5" class="empty-cell">Aucun livre pour le moment.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($books as $b): ?>
            <?php
                $img = $b['image'] ?: 'test.png';
                $src = '/images/' . $img;

                $desc = $b['description'] ?? '';
                $short = mb_strlen($desc) > 90 ? mb_substr($desc, 0, 90) . '...' : $desc;

                $isAvail = (int)($b['is_available'] ?? 1) === 1;
            ?>
            <tr>
                <td>
                <img class="table-img" src="<?= htmlspecialchars($src) ?>" alt="couverture">
                </td>

                <td>
                <a class="link-edit" style="text-decoration:none;" href="/?page=book&id=<?= (int)$b['id'] ?>">
                    <?= htmlspecialchars($b['title']) ?>
                </a>
                </td>

                <td><?= htmlspecialchars($b['author']) ?></td>

                <td class="table-desc"><?= nl2br(htmlspecialchars($short)) ?></td>

                <td>
                <?php if ($isAvail): ?>
                    <span class="pill pill--ok">disponible</span>
                <?php else: ?>
                    <span class="pill pill--no">non dispo.</span>
                <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>
</section>
