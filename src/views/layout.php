<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? "TomTroc") ?></title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<?php require __DIR__ . '/partials/header.php'; ?>

<main class="page">
  <?= $content ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
