<section class="container books-page">
  <div class="books-head">
    <h1 class="title-xl books-title">Nos livres à l'échange</h1>

    <form class="books-search" method="get" action="/">
      <input type="hidden" name="page" value="books">
      <input
        class="books-search__input"
        type="text"
        name="q"
        value="<?= htmlspecialchars($q ?? '') ?>"
        placeholder="Rechercher un livre"
      >
    </form>
  </div>

  <div class="books-grids books-grid--page">
    <?php if (empty($books)): ?>
      <p>Aucun livre trouvé.</p>
    <?php else: ?>
      <?php foreach ($books as $b): ?>
        <?php
          $img = $b['image'] ?: 'test.png';
          $src = '/images/' . $img;
        ?>
        <a class="book-link" href="/?page=book&id=<?= (int)$b['id'] ?>">
          <article class="book-card">
            <div class="book-card__media">
              <img class="book-card__img" src="<?= htmlspecialchars($src) ?>" alt="Couverture">

              <?php if ((int)$b['is_available'] === 0): ?>
                <div class="book-card__badge">non dispo.</div>
              <?php endif; ?>
            </div>

            <div class="book-card__body">
              <div class="book-card__title"><?= htmlspecialchars($b['title']) ?></div>
              <div class="book-card__author"><?= htmlspecialchars($b['author']) ?></div>
              <div class="book-card__seller">Vendu par : <span><?= htmlspecialchars($b['seller']) ?></span></div>
            </div>
          </article>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</section>
