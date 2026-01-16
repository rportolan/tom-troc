<header class="header">
  <div class="container header__inner">
    <?php $isAuth = !empty($_SESSION['user']); ?>

    <a href="/?page=<?= $isAuth ? 'books' : 'home' ?>" class="brand">
      <img src="/images/logo.svg" alt="logo tom troc">
    </a>

    <nav class="nav">
      <?php if (!$isAuth): ?>
        <a href="/?page=login" class="nav__link">Connexion</a>
        <a href="/?page=register" class="nav__link nav__link--cta">S’inscrire</a>
      <?php else: ?>
        <a href="/?page=books" class="nav__link">Nos livres à l'échange</a>
        <a href="/?page=messages" class="nav__link nav__link--icon">💬 Messagerie <span class="badge">1</span></a>
        <a href="/?page=account" class="nav__link nav__link--icon">👤 Mon compte</a>

        <form method="post" action="/?page=logout" style="display:inline;">
          <button class="nav__link" type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
            Déconnexion
          </button>
        </form>
      <?php endif; ?>
    </nav>
  </div>
</header>
