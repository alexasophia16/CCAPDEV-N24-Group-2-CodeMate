<main class="dashboard-content">
  <div class="dashboard-header">
    <h1>Welcome back, <?= e(strtok($user['full_name'], ' ')) ?>!</h1>
    <p class="dashboard-subtitle">Signed in as <?= e(role_label($user['role'])) ?>. Here's an overview of CodeMate.</p>
  </div>
  <section class="stat-grid" aria-label="Account overview">
    <?php foreach ($stats as [$label, $value]): ?>
      <article class="stat-card">
        <span class="stat-label"><?= e($label) ?></span>
        <strong class="stat-value"><?= (int)$value ?></strong>
      </article>
    <?php endforeach; ?>
  </section>
  <section class="dashboard-welcome-card">
    <h2>Learn together with CodeMate</h2>
    <p>Connect with fellow CCS students, find peer tutors, and make progress in your courses.</p>
    <?php if ($user['role'] === 'admin'): ?>
      <div class="dashboard-quick-links">
        <a class="role-btn primary" href="<?= e(url('admin-users')) ?>">Manage users</a>
        <a class="role-btn secondary" href="<?= e(url('admin-verify-tutors')) ?>">Review tutor applications</a>
      </div>
    <?php elseif ($user['role'] === 'tutee'): ?>
      <a class="role-btn primary" href="<?= e(url('find-tutor')) ?>">Find a tutor</a>
    <?php else: ?>
      <a class="role-btn primary" href="<?= e(url('tutor-profile-manage')) ?>">Manage tutor profile</a>
    <?php endif; ?>
  </section>
</main>
