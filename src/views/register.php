<section class="container auth">
  <div class="auth__grid">
    <div class="auth__left">
      <h1 class="auth__title">Inscription</h1>

      <?php if (!empty($error)): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="auth-form" method="post" action="/?page=register_post">
        <label class="label">Pseudo</label>
        <input class="input" type="text" name="pseudo" placeholder="ex: Alexlecture" required>

        <label class="label">Adresse email</label>
        <input class="input" type="email" name="email" placeholder="ex: nom@mail.com" required>

        <label class="label">Mot de passe</label>
        <input class="input" type="password" name="password" placeholder="••••••••" required>

        <button class="btn btn--primary auth-form__btn" type="submit">Créer mon compte</button>

        <div class="auth__links">
          <a class="link" href="/?page=login">J’ai déjà un compte</a>
        </div>
      </form>
    </div>

    <div class="auth__right">
      <div class="auth__imageWrap">
        <img src="/images/image-auth.jpg" alt="Visuel">
      </div>
    </div>
  </div>
</section>
