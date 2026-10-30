<main class="dashboard-content">
  <div class="dashboard-header">
    <h1>Welcome back, <?= e(strtok($user['full_name'], ' ')) ?>!</h1>
    <p class="dashboard-subtitle">
      Signed in as <?= e(role_label($user['role'])) ?>. Use the menu on the left to get around.
    </p>
  </div>
</main>
