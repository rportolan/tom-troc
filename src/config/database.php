<?php

return [
  'host' => $_ENV['DB_HOST'] ?? 'db',
  'port' => $_ENV['DB_PORT'] ?? '3306',
  'name' => $_ENV['DB_NAME'] ?? 'book_app',
  'user' => $_ENV['DB_USER'] ?? 'book_user',
  'pass' => $_ENV['DB_PASSWORD'] ?? 'book_pass',
  'charset' => 'utf8mb4',
];
