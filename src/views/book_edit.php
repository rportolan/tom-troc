<section class="container bookedit-page">

  <a class="back-link" href="/?page=account">← retour</a>

  <h1 class="bookedit-title"><?= htmlspecialchars($title) ?></h1>

  <?php if (!empty($success)): ?>
    <div class="flash flash--ok"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="flash flash--err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="bookedit-card">
    <form method="post" action="/?page=book_save" class="bookedit-form" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">

      <div class="bookedit-grid">
        <!-- Gauche : photo -->
        <div class="bookedit-photo">
  <div class="bookedit-photo__label">Photo</div>

  <?php
    $img = $book['image'] ?? 'test.png';
    if ($img === '') $img = 'test.png';
    $src = '/images/' . $img;
  ?>

  <img id="bookPreview" class="bookedit-photo__img" src="<?= htmlspecialchars($src) ?>" alt="Couverture">

  <label class="bookedit-photo__action">
    Modifier la photo
    <input
      id="bookImageInput"
      type="file"
      name="image"
      accept="image/png,image/jpeg,image/webp"
      hidden
    >
  </label>

  <div class="hint">jpg / png / webp (2 Mo max). Laisse vide pour garder l'image.</div>
</div>


        <!-- Droite : champs -->
        <div class="bookedit-fields">
          <div class="field">
            <label>Titre</label>
            <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>">
          </div>

          <div class="field">
            <label>Auteur</label>
            <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>">
          </div>

          <div class="field field--textarea">
            <label>Commentaire</label>
            <textarea name="description" rows="9"><?= htmlspecialchars($book['description']) ?></textarea>
          </div>

          <div class="field">
            <label>Disponibilité</label>
            <select name="is_available">
              <option value="1" <?= ((int)$book['is_available'] === 1) ? 'selected' : '' ?>>disponible</option>
              <option value="0" <?= ((int)$book['is_available'] === 0) ? 'selected' : '' ?>>non dispo.</option>
            </select>
          </div>

          <button class="btn-primary-wide" type="submit">Valider</button>
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

      if (lastUrl) URL.revokeObjectURL(lastUrl);
      lastUrl = URL.createObjectURL(file);
      preview.src = lastUrl;
    });
  })();
</script>


