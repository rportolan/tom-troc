<?php
declare(strict_types=1);

final class HomeController
{
    public function __construct(private BookRepository $books) {}

    public function index(): void
    {
        $title = "Accueil";
        $latestBooks = $this->books->latest(4);

        ob_start();
        require __DIR__ . '/../views/home.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }
}
