<?php
declare(strict_types=1);

session_start();

$page = $_GET['page'] ?? 'home';

// Pages publiques
$publicPages = ['home', 'login', 'login_post', 'register', 'register_post'];

if (!in_array($page, $publicPages, true) && empty($_SESSION['user'])) {
    header('Location: /?page=login');
    exit;
}

// Env + DB
require_once __DIR__ . '/../src/core/Env.php';
Env::load(__DIR__ . '/../src/config/.env');

require_once __DIR__ . '/../src/config/pdo.php';

// Core
require_once __DIR__ . '/../src/core/Auth.php';

// Entities
require_once __DIR__ . '/../src/models/entities/User.php';
require_once __DIR__ . '/../src/models/entities/Book.php';
require_once __DIR__ . '/../src/models/entities/Conversation.php';
require_once __DIR__ . '/../src/models/entities/Message.php';

// Repositories
require_once __DIR__ . '/../src/models/repositories/UserRepository.php';
require_once __DIR__ . '/../src/models/repositories/BookRepository.php';
require_once __DIR__ . '/../src/models/repositories/ConversationRepository.php';
require_once __DIR__ . '/../src/models/repositories/MessageRepository.php';

// Controllers
require_once __DIR__ . '/../src/controllers/HomeController.php';
require_once __DIR__ . '/../src/controllers/BookController.php';
require_once __DIR__ . '/../src/controllers/MessageController.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/AccountController.php';
require_once __DIR__ . '/../src/controllers/ProfileController.php';

// DI simple
$usersRepo = new UserRepository($pdo);
$booksRepo = new BookRepository($pdo);
$conversationsRepo = new ConversationRepository($pdo);
$messagesRepo = new MessageRepository($pdo);

switch ($page) {
    case 'home':
        (new HomeController($booksRepo))->index();
        break;

    case 'books':
        (new BookController($booksRepo))->index();
        break;

    case 'book':
        (new BookController($booksRepo))->show();
        break;

    case 'book_create':
        (new BookController($booksRepo))->create();
        break;

    case 'book_edit':
        (new BookController($booksRepo))->edit();
        break;

    case 'book_save':
        (new BookController($booksRepo))->save();
        break;

    case 'book_delete':
        (new BookController($booksRepo))->delete();
        break;

    case 'messages':
        (new MessageController($usersRepo, $conversationsRepo, $messagesRepo))->index();
        break;

    case 'message_send':
        (new MessageController($usersRepo, $conversationsRepo, $messagesRepo))->send();
        break;

    case 'login':
        (new AuthController($usersRepo))->loginForm();
        break;

    case 'login_post':
        (new AuthController($usersRepo))->login();
        break;

    case 'register':
        (new AuthController($usersRepo))->registerForm();
        break;

    case 'register_post':
        (new AuthController($usersRepo))->register();
        break;

    case 'logout':
        (new AuthController($usersRepo))->logout();
        break;

    case 'account':
        (new AccountController($usersRepo, $booksRepo))->index();
        break;

    case 'account_update':
        (new AccountController($usersRepo, $booksRepo))->update();
        break;

    case 'profile':
        (new ProfileController($usersRepo, $booksRepo))->show();
        break;

    default:
        http_response_code(404);
        $title = "Page introuvable";
        ob_start();
        require __DIR__ . '/../src/views/404.php';
        $content = ob_get_clean();
        require __DIR__ . '/../src/views/layout.php';
        break;
}
