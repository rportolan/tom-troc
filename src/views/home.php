<section class="hero container">
  <div class="hero__left">
    <h1 class="title-xl">Rejoignez nos<br>lecteurs passionnés</h1>
    <p class="text">
      Donnez une nouvelle vie à vos livres en les échangeant avec d’autres amoureux de la lecture.
      Nous croyons en la magie du partage de connaissances et d’histoires à travers les livres.
    </p>
    <a class="btn btn--primary" href="/?page=books">Découvrir</a>
  </div>

  <div class="hero__right">
    <div class="hero__imgWrap">
      <img class="hero__img" src="/images/test.png" alt="Photo">
      <div class="hero__credit">Hamza</div>
    </div>
  </div>
</section>

<section class="section section-books container">
  <h2 class="title-md center">Les derniers livres ajoutés</h2>

  <div class="books-grid">
    <?php if (empty($latestBooks)): ?>
      <p class="center">Aucun livre pour le moment.</p>
    <?php else: ?>
      <?php foreach ($latestBooks as $b): ?>
        <?php
          $img = !empty($b['image']) ? $b['image'] : 'test.png';
          $src = '/images/' . $img;
        ?>
        <a class="book-link" href="/?page=book&id=<?= (int)$b['id'] ?>">
          <article class="book-card">
            <img class="book-card__img" src="<?= htmlspecialchars($src) ?>" alt="Couverture">

            <div class="book-card__body">
              <div class="book-card__title"><?= htmlspecialchars($b['title']) ?></div>
              <div class="book-card__author"><?= htmlspecialchars($b['author']) ?></div>
              <div class="book-card__seller">
                Vendu par : <span><?= htmlspecialchars($b['seller']) ?></span>
              </div>
            </div>
          </article>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="center mt-24">
    <a class="btn btn--primary" href="/?page=books">Voir tous les livres</a>
  </div>
</section>


<section class="section section-faq container">
  <h2 class="title-md center">Comment ça marche ?</h2>
  <p class="subtext center">Échanger des livres avec TomTroc c’est simple et amusant ! Suivez ces étapes pour commencer :</p>

  <div class="steps">
    <div class="step-card">
      <p>Inscrivez-vous gratuitement sur notre plateforme.</p>
    </div>
    <div class="step-card">
      <p>Ajoutez les livres que vous souhaitez échanger à votre profil.</p>
    </div>
    <div class="step-card">
      <p>Parcourez les livres disponibles chez d’autres membres.</p>
    </div>
    <div class="step-card">
      <p>Proposez un échange et discutez avec d’autres passionnés de lecture.</p>
    </div>
  </div>

  <div class="center mt-24">
    <a class="btn btn--outline" href="/?page=books">Voir tous les livres</a>
  </div>
</section>

<section class="values">
  <div class="values__bg">
    <img src="/images/image-section.jpg" alt="Bandeau">
  </div>

  <div class="container values__content">
    <div class="values__text">
      <h2 class="title-md">Nos valeurs</h2>
      <p class="text">
        Chez Tom Troc, nous mettons l'accent sur le partage, la découverte et la communauté.
        Nos valeurs sont ancrées dans notre passion pour les livres et notre désir de créer des liens entre lecteurs.
      </p>
      <p class="text">
        Nous croyons en la puissance des histoires pour rassembler les gens et inspirer des conversations enrichissantes.
      </p>
      <p class="text">
        Notre association a été fondée avec une conviction profonde : chaque livre mérite d'être lu et partagé.
      </p>
      <p class="text">
        Nous sommes passionnés par la création d'une plateforme conviviale qui permet aux lecteurs de se connecter,
        de partager leurs découvertes littéraires et d'échanger des livres qui attendent patiemment sur les étagères.
      </p>
      <div class="values__signature">L’équipe Tom Troc</div>
    </div>

    <div class="values__mark">
      <img src="/images/illustration_hearth.svg" alt="">
    </div>
  </div>
</section>
