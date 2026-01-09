<?php

session_start();

$page = $_GET['page'] ?? 'home';

// Pages publiques
$publicPages = ['home', 'login', 'login_post', 'register', 'register_post'];

// Si page privée et pas connecté => redirection login
if (!in_array($page, $publicPages, true) && empty($_SESSION['user'])) {
    header('Location: /?page=login');
    exit;
}

// Mini loader .env (junior)
$envPath = __DIR__ . '/../src/config/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($k)] = trim($v, "\"' ");
    }
}

require_once __DIR__ . '/../src/config/pdo.php';
require_once __DIR__ . '/../src/controllers/HomeController.php';
require_once __DIR__ . '/../src/controllers/BookController.php';
require_once __DIR__ . '/../src/controllers/MessageController.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

switch ($page) {
    case 'books':
        $controller = new BookController($pdo);
        $controller->index();
        break;

    case 'book':
        $controller = new BookController($pdo);
        $controller->show();
        break;

    case 'messages':
        $controller = new MessageController($pdo);
        $controller->index();
        break;

    case 'login':
        $controller = new AuthController($pdo);
        $controller->loginForm();
        break;

    case 'login_post':
        $controller = new AuthController($pdo);
        $controller->login();
        break;

    case 'register':
        require_once __DIR__ . '/../src/controllers/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->registerForm();
        break;

    case 'register_post':
        require_once __DIR__ . '/../src/controllers/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->register();
        break;
    
    case 'logout':
        $controller = new AuthController($pdo);
        $controller->logout();
        break;

    case 'account':
        require_once __DIR__ . '/../src/controllers/AccountController.php';
        $controller = new AccountController($pdo);
        $controller->index();
        break;

    case 'account_update':
        require_once __DIR__ . '/../src/controllers/AccountController.php';
        $controller = new AccountController($pdo);
        $controller->update();
        break;

    case 'book_delete':
        require_once __DIR__ . '/../src/controllers/BookController.php';
        $controller = new BookController($pdo);
        $controller->delete();
        break;
    
    case 'book_create':
        require_once __DIR__ . '/../src/controllers/BookController.php';
        $controller = new BookController($pdo);
        $controller->create();
        break;


    case 'book_edit':
        require_once __DIR__ . '/../src/controllers/BookController.php';
        $controller = new BookController($pdo);
        $controller->edit();
        break;

    case 'book_save':
        require_once __DIR__ . '/../src/controllers/BookController.php';
        $controller = new BookController($pdo);
        $controller->save();
        break;

    case 'profile':
        require_once __DIR__ . '/../src/controllers/ProfileController.php';
        $controller = new ProfileController($pdo);
        $controller->show();
        break;

    case 'messages':
    $controller = new MessageController($pdo);
    $controller->index();
    break;

    case 'message_send':
        $controller = new MessageController($pdo);
        $controller->send();
        break;

    case 'home':
        $controller = new HomeController($pdo);
        $controller->index();
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
