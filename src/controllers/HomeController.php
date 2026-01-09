<?php

class HomeController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        $title = "Accueil";

        // 4 derniers livres ajoutés (avec vendeur + image)
        $stmt = $this->pdo->prepare("
            SELECT 
                b.id,
                b.title,
                b.author,
                b.image,
                b.is_available,
                u.pseudo AS seller
            FROM books b
            JOIN users u ON u.id = b.user_id
            ORDER BY b.id DESC
            LIMIT 4
        ");
        $stmt->execute();
        $latestBooks = $stmt->fetchAll();

        ob_start();
        require __DIR__ . '/../views/home.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }
}
