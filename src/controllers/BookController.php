<?php
declare(strict_types=1);

final class BookController
{
    public function __construct(private BookRepository $books) {}

    public function index(): void
    {
        Auth::requireLogin();

        $title = "Nos livres à l'échange";
        $q = trim($_GET['q'] ?? '');

        $books = $this->books->searchWithSeller($q);

        ob_start();
        require __DIR__ . '/../views/books.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function create(): void
    {
        Auth::requireLogin();
        $title = "Ajouter un livre";

        $book = [
            'id' => 0,
            'title' => '',
            'author' => '',
            'description' => '',
            'image' => 'test.png',
            'is_available' => 1,
        ];

        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/book_edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function edit(): void
    {
        Auth::requireLogin();

        $title = "Modifier les informations";
        $userId = Auth::userId();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=account&error=Livre introuvable'); exit;
        }

        $entity = $this->books->findOwnedById($id, $userId);
        if (!$entity) {
            header('Location: /?page=account&error=Livre introuvable'); exit;
        }

        $book = $entity->toArrayForViews();
        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/book_form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function save(): void
    {
        Auth::requireLogin();

        $userId = Auth::userId();

        $id = (int)($_POST['id'] ?? 0);
        $bookTitle = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isAvailable = (int)($_POST['is_available'] ?? 1);

        if ($bookTitle === '' || $author === '') {
            if ($id > 0) header('Location: /?page=book_edit&id=' . $id . '&error=Titre et auteur obligatoires');
            else header('Location: /?page=book_create&error=Titre et auteur obligatoires');
            exit;
        }

        $newImage = $this->handleBookImageUpload();
        $imageToSave = $newImage ?? 'test.png';

        if ($id > 0) {
            $current = $this->books->findOwnedById($id, $userId);
            if (!$current) {
                header('Location: /?page=account&error=Livre introuvable'); exit;
            }

            $imageToSave = $newImage ?? ($current->image() ?: 'test.png');

            $updated = new Book($id, $userId, $bookTitle, $author, $description, $imageToSave, $isAvailable);
            $this->books->update($updated);

            if ($newImage && $current->image() && $current->image() !== 'test.png') {
                $oldPath = __DIR__ . '/../../public/images/' . $current->image();
                if (is_file($oldPath)) @unlink($oldPath);
            }

            header('Location: /?page=account&success=Livre modifié'); exit;
        }

        $created = new Book(0, $userId, $bookTitle, $author, $description, $imageToSave, $isAvailable);
        $this->books->create($created);

        header('Location: /?page=account&success=Livre ajouté'); exit;
    }

    public function show(): void
    {
        Auth::requireLogin();

        $title = "Détail du livre";
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=books&error=Livre introuvable'); exit;
        }

        $row = $this->books->findByIdWithOwner($id);
        if (!$row) {
            header('Location: /?page=books&error=Livre introuvable'); exit;
        }

        // On garde EXACTEMENT la structure attendue par la vue
        $book = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'author' => $row['author'],
            'description' => $row['description'],
            'image' => $row['image'] ?: 'test.png',
            'is_available' => (int)$row['is_available'],
            'user_id' => (int)$row['user_id'],
            'owner' => [
                'pseudo' => $row['owner_pseudo'],
                'avatar' => $row['owner_avatar'] ? '/images/' . $row['owner_avatar'] : '/images/test.png',
            ],
        ];

        ob_start();
        require __DIR__ . '/../views/book_show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    public function delete(): void
    {
        Auth::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $userId = Auth::userId();

        if ($id <= 0) {
            header('Location: /?page=account&error=Livre introuvable'); exit;
        }

        $image = $this->books->deleteOwned($id, $userId);
        if ($image === null) {
            header('Location: /?page=account&error=Livre introuvable'); exit;
        }

        if ($image && $image !== 'test.png') {
            $path = __DIR__ . '/../../public/images/' . $image;
            if (is_file($path)) @unlink($path);
        }

        header('Location: /?page=account&success=Livre supprimé'); exit;
    }

    private function handleBookImageUpload(): ?string
    {
        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) return null;
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) return null;

        $maxSize = 2 * 1024 * 1024;
        if (!empty($_FILES['image']['size']) && $_FILES['image']['size'] > $maxSize) return null;

        $tmp = $_FILES['image']['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) return null;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) return null;

        $ext = $allowed[$mime];
        $dir = __DIR__ . '/../../public/images/books';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($tmp, $dest)) return null;
        return 'books/' . $name;
    }
}
