<?php

class BookController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function requireLogin()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /?page=login&error=Veuillez vous connecter');
            exit;
        }
    }

    public function index()
    {
        $this->requireLogin();

        $title = "Nos livres à l'échange";
        $q = trim($_GET['q'] ?? '');

        $sql = "
            SELECT 
                b.id,
                b.title,
                b.author,
                b.image,
                b.is_available,
                u.pseudo AS seller
            FROM books b
            JOIN users u ON u.id = b.user_id
            WHERE 1=1
        ";
        $params = [];

        if ($q !== '') {
            $sql .= " AND (b.title LIKE :q OR b.author LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $sql .= " ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll();

        ob_start();
        require __DIR__ . '/../views/books.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    // ✅ NOUVELLE PAGE : ajout
    public function create()
    {
        $this->requireLogin();

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

    // ✅ PAGE : modification
    public function edit()
    {
        $this->requireLogin();

        $title = "Modifier les informations";
        $userId = (int)$_SESSION['user']['id'];
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=account&error=Livre introuvable');
            exit;
        }

        $stmt = $this->pdo->prepare("
            SELECT id, title, author, description, image, is_available
            FROM books
            WHERE id = :id AND user_id = :uid
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $book = $stmt->fetch();

        if (!$book) {
            header('Location: /?page=account&error=Livre introuvable');
            exit;
        }

        $error = $_GET['error'] ?? '';

        ob_start();
        require __DIR__ . '/../views/book_form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    public function save()
    {
        
        $this->requireLogin();

        $userId = (int)$_SESSION['user']['id'];

        $id = (int)($_POST['id'] ?? 0);
        $bookTitle = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isAvailable = (int)($_POST['is_available'] ?? 1);

        if ($bookTitle === '' || $author === '') {
            // redirige vers la bonne page (create ou edit)
            if ($id > 0) {
                header('Location: /?page=book_edit&id=' . $id . '&error=Titre et auteur obligatoires');
            } else {
                header('Location: /?page=book_create&error=Titre et auteur obligatoires');
            }
            exit;
        }

        $newImage = $this->handleBookImageUpload();

        // UPDATE
        if ($id > 0) {
            $currentStmt = $this->pdo->prepare("
                SELECT image
                FROM books
                WHERE id = :id AND user_id = :uid
                LIMIT 1
            ");
            $currentStmt->execute([':id' => $id, ':uid' => $userId]);
            $current = $currentStmt->fetch();

            if (!$current) {
                header('Location: /?page=account&error=Livre introuvable');
                exit;
            }

            $imageToSave = $newImage ?? ($current['image'] ?: 'test.png');

            $stmt = $this->pdo->prepare("
                UPDATE books
                SET title = :title,
                    author = :author,
                    description = :description,
                    is_available = :avail,
                    image = :img
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                ':title' => $bookTitle,
                ':author' => $author,
                ':description' => $description,
                ':avail' => $isAvailable,
                ':img' => $imageToSave,
                ':id' => $id,
                ':uid' => $userId,
            ]);

            // supprime ancienne image si remplacée
            if ($newImage && !empty($current['image']) && $current['image'] !== 'test.png') {
                $oldPath = __DIR__ . '/../../public/images/' . $current['image'];
                if (is_file($oldPath)) @unlink($oldPath);
            }

            header('Location: /?page=account&success=Livre modifié');
            exit;
        }

        // INSERT
        $imageToSave = $newImage ?? 'test.png';

        $stmt = $this->pdo->prepare("
            INSERT INTO books (user_id, title, author, description, image, is_available)
            VALUES (:uid, :title, :author, :description, :img, :avail)
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':title' => $bookTitle,
            ':author' => $author,
            ':description' => $description,
            ':img' => $imageToSave,
            ':avail' => $isAvailable,
        ]);

        header('Location: /?page=account&success=Livre ajouté');
        exit;
    }

    public function show()
    {
        $this->requireLogin();

        $title = "Détail du livre";
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=books&error=Livre introuvable');
            exit;
        }

        $stmt = $this->pdo->prepare("
            SELECT 
                b.id,
                b.title,
                b.author,
                b.description,
                b.image,
                b.is_available,
                b.user_id,
                u.pseudo AS owner_pseudo,
                u.avatar AS owner_avatar
            FROM books b
            JOIN users u ON u.id = b.user_id
            WHERE b.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            header('Location: /?page=books&error=Livre introuvable');
            exit;
        }

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

    public function delete()
    {
        $this->requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $userId = (int)$_SESSION['user']['id'];

        if ($id <= 0) {
            header('Location: /?page=account&error=Livre introuvable');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT image FROM books WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $book = $stmt->fetch();

        if (!$book) {
            header('Location: /?page=account&error=Livre introuvable');
            exit;
        }

        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $id, ':uid' => $userId]);

        if (!empty($book['image']) && $book['image'] !== 'test.png') {
            $path = __DIR__ . '/../../public/images/' . $book['image'];
            if (is_file($path)) @unlink($path);
        }

        header('Location: /?page=account&success=Livre supprimé');
        exit;
    }

    private function handleBookImageUpload(): ?string
    {
        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $maxSize = 2 * 1024 * 1024;
        if (!empty($_FILES['image']['size']) && $_FILES['image']['size'] > $maxSize) {
            return null;
        }

        $tmp = $_FILES['image']['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            return null;
        }

        $ext = $allowed[$mime];

        $dir = __DIR__ . '/../../public/images/books';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }

        return 'books/' . $name;
    }
}
