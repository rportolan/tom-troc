<section class="container single">
  <div class="breadcrumb">
    <a class="breadcrumb__link" href="/?page=books">Nos livres</a>
    <span class="breadcrumb__sep">></span>
    <span class="breadcrumb__current"><?= htmlspecialchars($book['title']) ?></span>
  </div>

  <div class="single-grid">
    <div class="single-image">
        <?php
        $img = $book['image'] ?? 'test.png';
        $src = '/images/' . $img;
        ?>
      <img src="<?= htmlspecialchars($src) ?>" alt="Couverture du livre">

    </div>

    <div class="single-panel">
      <h1 class="single-title"><?= htmlspecialchars($book['title']) ?></h1>
      <div class="single-author">par <?= htmlspecialchars($book['author']) ?></div>

      <div class="single-divider"></div>

      <div class="single-section-title">DESCRIPTION</div>
      <p class="single-desc">
        <?= nl2br(htmlspecialchars($book['description'])) ?>
      </p>

      <div class="single-section-title">PROPRIÉTAIRE</div>
      <div class="owner">
        <a href="/?page=profile&id=<?= (int)$book['user_id'] ?>">
          <img class="owner__avatar" src="<?= htmlspecialchars($book['owner']['avatar']) ?>" alt="Avatar">
        </a>

        <a class="owner__name" href="/?page=profile&id=<?= (int)$book['user_id'] ?>" style="text-decoration:none;">
          <?= htmlspecialchars($book['owner']['pseudo']) ?>
        </a>

      </div>

      <?php $isMine = !empty($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$book['user_id']; ?>

      <?php if (!$isMine): ?>
        <a class="btn btn--primary btn--wide" href="/?page=messages&to=<?= urlencode($book['owner']['pseudo']) ?>">
          Envoyer un message
        </a>
      <?php else: ?>
        <div class="hint" style="margin-top:16px;">C’est votre livre.</div>
      <?php endif; ?>

    </div>
  </div>
</section>
