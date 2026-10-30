<?php if (current_user()): ?>
  <main class="role-page">
    <div class="role-card">
      <h1><?= e($title) ?></h1>
      <p><?= e($message) ?></p>
      <p><a href="<?= e(url('dashboard')) ?>">Back to the dashboard</a></p>
    </div>
  </main>
<?php else: ?>
  <h1><?= e($title) ?></h1>
  <p><?= e($message) ?></p>
  <p><a href="<?= e(url('login')) ?>">Go to the login page</a></p>
<?php endif; ?>
