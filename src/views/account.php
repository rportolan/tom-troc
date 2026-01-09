<section class="container account-page">
  <h1 class="account-title">Mon compte</h1>

  <?php if (!empty($success)): ?>
    <div class="flash flash--ok"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="flash flash--err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="account-top">
    <!-- Carte profil (gauche) -->
    <div class="card account-profile">
      <div class="account-profile__avatarWrap">
        <img class="account-profile__avatar" src="/images/test.png" alt="avatar">
        <a class="account-profile__edit" href="#">modifier</a>
      </div>

      <div class="account-profile__sep"></div>

      <div class="account-profile__name"><?= htmlspecialchars($user['pseudo'] ?? 'Utilisateur') ?></div>
      <div class="account-profile__since"><?= htmlspecialchars($memberSince ?? 'Membre depuis -') ?></div>

      <div class="account-profile__lib">
        <div class="account-profile__libTitle">BIBLIOTHÈQUE</div>
        <div class="account-profile__libCount">📚 <?= count($books) ?> livres</div>
      </div>
    </div>

    <!-- Infos (droite) -->
    <div class="card account-infos">
      <div class="account-infos__title">Vos informations personnelles</div>

      <form method="post" action="/?page=account_update" class="account-form">
        <div class="field">
          <label>Adresse email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>

        <div class="field">
          <label>Mot de passe</label>
          <input type="password" name="password" placeholder="••••••••">
          <small class="hint">Laisse vide si tu ne veux pas changer</small>
        </div>

        <div class="field">
          <label>Pseudo</label>
          <input type="text" name="pseudo" value="<?= htmlspecialchars($user['pseudo'] ?? '') ?>">
        </div>

        <button class="btn-outline" type="submit">Enregistrer</button>
      </form>
    </div>
  </div>

  <!-- Table livres -->
   <div class="account-actions">
      <a class="btn btn--primary" href="/?page=book_create">+ Ajouter un livre</a>
    </div>


  <div class="card account-tableWrap">
    <table class="account-table">
      <thead>
        <tr>
          <th>PHOTO</th>
          <th>TITRE</th>
          <th>AUTEUR</th>
          <th>DESCRIPTION</th>
          <th>DISPONIBILITÉ</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($books)): ?>
          <tr>
            <td colspan="6" class="empty-cell">Aucun livre pour le moment.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($books as $b): ?>
            <tr>
              <td>
                <?php
                  $img = $b['image'] ?? 'test.png';
                  if ($img === '') $img = 'test.png';
                  // si tu stockes déjà "test.png" sans sous-dossier, ok.
                  // si tu uploades dans books/, on mettra le chemin ci-dessous.
                  $src = str_starts_with($img, 'books/') ? '/images/' . $img : '/images/' . $img;
                ?>
                <img class="table-img" src="<?= htmlspecialchars($src) ?>" alt="couverture">
              </td>
              <td><?= htmlspecialchars($b['title']) ?></td>
              <td><?= htmlspecialchars($b['author']) ?></td>
              <td class="table-desc">
                <?php
                  $desc = $b['description'] ?? '';
                  $short = mb_strlen($desc) > 80 ? mb_substr($desc, 0, 80) . '...' : $desc;
                ?>
                <?= nl2br(htmlspecialchars($short)) ?>
              </td>
              <td>
                <?php if ((int)$b['is_available'] === 1): ?>
                  <span class="pill pill--ok">disponible</span>
                <?php else: ?>
                  <span class="pill pill--no">non dispo.</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="link-edit" href="/?page=book_edit&id=<?= (int)$b['id'] ?>">Éditer</a>
                <a class="link-del" href="/?page=book_delete&id=<?= (int)$b['id'] ?>" onclick="return confirm('Supprimer ce livre ?');">Supprimer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <!-- Modal livre -->
<div class="modal" id="bookModal" hidden>
  <div class="modal__overlay" data-close="1"></div>

  <div class="modal__content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <h2 id="modalTitle">Ajouter un livre</h2>

    <form method="post" action="/?page=book_save" class="modal-form" enctype="multipart/form-data">

      <input type="hidden" name="id" id="book_id">

      <div class="field">
        <label>Titre</label>
        <input type="text" name="title" id="book_title" required>
      </div>

      <div class="field">
        <label>Auteur</label>
        <input type="text" name="author" id="book_author" required>
      </div>

      <div class="field">
        <label>Description</label>
        <textarea name="description" id="book_description" rows="4"></textarea>
      </div>

      <div class="field">
        <label>Image (jpg/png/webp)</label>
        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
        <small class="hint">Laisse vide pour garder l’image actuelle.</small>
      </div>

      <div class="field">
        <label>Disponibilité</label>
        <select name="is_available" id="book_available">
          <option value="1">Disponible</option>
          <option value="0">Non disponible</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-outline" id="closeModalBtn">Annuler</button>
        <button type="submit" class="btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

</section>

<script>
  function openBookModal(book = null) {
    const modal = document.getElementById('bookModal');
    if (!modal) return;

    modal.hidden = false;

    // champs
    const titleEl = document.getElementById('modalTitle');
    const idEl = document.getElementById('book_id');
    const tEl = document.getElementById('book_title');
    const aEl = document.getElementById('book_author');
    const dEl = document.getElementById('book_description');
    const avEl = document.getElementById('book_available');

    if (book) {
      titleEl.textContent = 'Modifier le livre';
      idEl.value = book.id;
      tEl.value = book.title || '';
      aEl.value = book.author || '';
      dEl.value = book.description || '';
      avEl.value = String(book.is_available ?? 1);
    } else {
      titleEl.textContent = 'Ajouter un livre';
      idEl.value = '';
      tEl.value = '';
      aEl.value = '';
      dEl.value = '';
      avEl.value = '1';
    }

    // focus UX
    setTimeout(() => tEl.focus(), 0);
  }

  function closeBookModal() {
    const modal = document.getElementById('bookModal');
    if (!modal) return;
    modal.hidden = true;
  }

  // fermetures (overlay + bouton)
  document.addEventListener('click', (e) => {
    if (e.target && e.target.dataset && e.target.dataset.close === '1') {
      closeBookModal();
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    const closeBtn = document.getElementById('closeModalBtn');
    if (closeBtn) closeBtn.addEventListener('click', closeBookModal);
  });

  // ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeBookModal();
  });
</script>


