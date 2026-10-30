<?php
$success = get_flash('success');
$error = get_flash('error');
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="frontend/styles/base.css" />
    <link rel="stylesheet" href="frontend/styles/sidebar.css" />
    <link rel="stylesheet" href="frontend/styles/topbar.css" />
    <link rel="stylesheet" href="frontend/styles/app.css" />
    <?php foreach ($styles as $style): ?>
      <link rel="stylesheet" href="frontend/styles/<?= e($style) ?>.css" />
    <?php endforeach; ?>
  </head>
  <body class="app-layout">
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <?php require APP_ROOT . '/frontend/views/partials/sidebar.php'; ?>

    <div class="main-column">
      <?php require APP_ROOT . '/frontend/views/partials/topbar.php'; ?>

      <?php if ($success): ?>
        <div class="flash flash-success" role="status"><?= e($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="flash flash-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <?= $content ?>
    </div>

    <script src="frontend/scripts/layout.js"></script>
    <?php foreach ($scripts as $script): ?>
      <script src="frontend/scripts/<?= e($script) ?>.js"></script>
    <?php endforeach; ?>
  </body>
</html>
