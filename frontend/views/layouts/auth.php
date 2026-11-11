<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="frontend/styles/sign-up.css?v=<?= (int) @filemtime(APP_ROOT . '/frontend/styles/sign-up.css') ?>" />
  </head>
  <body class="auth-layout">
    <div class="wrapper">
      <a class="auth-brand" href="<?= e(url('login')) ?>">CodeMate <span>CCS - DLSU</span></a>
      <?= $content ?>
    </div>
  <script src="frontend/scripts/auth-page.js"></script>
  </body>
</html>
