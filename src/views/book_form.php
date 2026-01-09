<section class="container book-edit-page">
  <a class="back-link" href="/?page=account">← retour</a>

  <h1 class="page-title"><?= htmlspecialchars($title ?? 'Livre') ?></h1>

  <?php if (!empty($error)): ?>
    <div class="flash flash--err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php
    $img = $book['image'] ?? 'test.png';
    if ($img === '') $img = 'test.png';
    $src = '/images/' . $img;
  ?>

  <div class="card book-edit-card">
    <form class="book-edit-form" method="post" action="/?page=book_save" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= (int)($book['id'] ?? 0) ?>">

      <div class="book-edit-grid">
        <!-- gauche -->
        <div class="book-edit-left">
          <div class="book-edit-photoLabel">Photo</div>
          <div class="book-edit-photo">
            <img id="bookPreview" class="book-edit-img" src="<?= htmlspecialchars($src) ?>" alt="Couverture">
          </div>

          <label class="book-edit-file">
            Modifier la photo
            <input id="bookImageInput" type="file" name="image" accept="image/png,image/jpeg,image/webp" hidden>
          </label>

          <div class="hint">jpg / png / webp (2 Mo max). Laisse vide pour garder l'image.</div>
        </div>

        <!-- droite -->
        <div class="book-edit-right">
          <div class="field">
            <label>Titre</label>
            <input class="input" type="text" name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>" required>
          </div>

          <div class="field">
            <label>Auteur</label>
            <input class="input" type="text" name="author" value="<?= htmlspecialchars($book['author'] ?? '') ?>" required>
          </div>

          <div class="field">
            <label>Commentaire</label>
            <textarea class="textarea" name="description" rows="7"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
          </div>

          <div class="field">
            <label>Disponibilité</label>
            <select class="select" name="is_available">
              <option value="1" <?= ((int)($book['is_available'] ?? 1) === 1) ? 'selected' : '' ?>>disponible</option>
              <option value="0" <?= ((int)($book['is_available'] ?? 1) === 0) ? 'selected' : '' ?>>non disponible</option>
            </select>
          </div>

          <button class="btn btn--primary btn--wide" type="submit">Valider</button>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
  (function () {
    const input = document.getElementById("bookImageInput");
    const preview = document.getElementById("bookPreview");
    if (!input || !preview) return;

    let lastUrl = null;

    input.addEventListener("change", () => {
      const file = input.files && input.files[0];
      if (!file) return;

      // sécurité : uniquement images
      if (!file.type.startsWith("image/")) return;

      if (lastUrl) URL.revokeObjectURL(lastUrl);
      lastUrl = URL.createObjectURL(file);
      preview.src = lastUrl;
    });

    // clean si on quitte la page
    window.addEventListener("beforeunload", () => {
      if (lastUrl) URL.revokeObjectURL(lastUrl);
    });
  })();
</script>

