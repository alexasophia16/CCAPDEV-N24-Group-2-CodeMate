<?php
$statusKeys = ['confirmed', 'pending', 'completed', 'cancelled'];
$statusTotal = array_sum($statusTotals);
$chartData = [
    'subjects' => ['labels' => array_keys($subjectTotals), 'values' => array_values($subjectTotals)],
    'status'   => ['labels' => array_map('ucfirst', $statusKeys), 'values' => array_map(fn($k) => (int) ($statusTotals[$k] ?? 0), $statusKeys)],
];
?>
<main class="dashboard-content">
  <div class="dashboard-header">
    <h1>Welcome back, <?= e(strtok($user['full_name'], ' ')) ?>!</h1>
    <p class="dashboard-subtitle">
      <?= $user['role'] === 'tutee' ? "Signed in as Student / Tutee. Here's an overview of CodeMate." : "Here's what's happening with your CodeMate activity." ?>
    </p>
  </div>

  <section class="stat-grid" aria-label="Account overview">
    <?php foreach ($stats as [$label, $value]): ?>
      <article class="stat-card">
        <span class="stat-label"><?= e($label) ?></span>
        <strong class="stat-value"><?= e((string) $value) ?></strong>
      </article>
    <?php endforeach; ?>
  </section>

  <?php if ($user['role'] !== 'admin'): ?>
  <section class="chart-row" aria-label="Booking charts">
    <article class="chart-card">
      <h2 class="chart-title"><?= $user['role'] === 'tutor' ? 'Your sessions by subject' : 'Bookings by subject' ?></h2>
      <?php if ($subjectTotals): ?>
        <div class="chart-canvas-wrap"><canvas id="chart-bookings-by-subject" role="img" aria-label="Bookings by subject"></canvas></div>
      <?php else: ?>
        <p class="dashboard-empty">No booking data available yet.</p>
      <?php endif; ?>
    </article>

    <article class="chart-card">
      <h2 class="chart-title">Bookings by status</h2>
      <?php if ($statusTotal > 0): ?>
        <div class="chart-canvas-wrap"><canvas id="chart-booking-status" role="img" aria-label="Bookings by status"></canvas></div>
      <?php else: ?>
        <p class="dashboard-empty">No booking data available yet.</p>
      <?php endif; ?>
    </article>
  </section>
  <script type="application/json" id="dashboard-chart-data"><?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <?php endif; ?>

  <?php if ($user['role'] === 'tutee'): ?>
    <section class="featured-tutors">
      <h2 class="section-title">Featured Tutors</h2>
      <?php if ($featuredTutors): ?>
        <div class="tutor-carousel">
          <?php foreach ($featuredTutors as $tutor): ?>
            <?php $initials = implode('', array_map(fn($part) => mb_substr($part, 0, 1), array_slice(preg_split('/\s+/', $tutor['full_name']), 0, 2))); ?>
            <article class="tutor-card">
              <div class="tutor-top">
                <div class="tutor-avatar"><?= e($initials) ?></div>
                <div class="tutor-info">
                  <a class="tutor-name" href="<?= e(url('tutor-profile', ['id' => $tutor['user_id']])) ?>"><?= e($tutor['full_name']) ?></a>
                  <div class="tutor-cred"><?= number_format($tutor['credibility'], 1) ?> · <?= (int)$tutor['sessions'] ?> sessions</div>
                </div>
                <span class="tutor-badge <?= $tutor['verification_status'] === 'verified' ? 'verified' : 'pending' ?>"><?= e(ucfirst($tutor['verification_status'])) ?></span>
              </div>
              <div class="tutor-tags">
                <?php foreach ($tutor['subjects'] as $subject): ?><span class="tag"><?= e($subject) ?></span><?php endforeach; ?>
              </div>
              <?php if ($tutor['verification_status'] === 'verified'): ?>
                <a class="btn-book" href="<?= e(url('book-consultation', ['tutor' => $tutor['user_id']])) ?>">Book session</a>
              <?php else: ?>
                <button class="btn-book" disabled>Not yet bookable</button>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?><p class="dashboard-empty">No tutors are available yet.</p><?php endif; ?>
    </section>
  <?php elseif ($user['role'] === 'tutor'): ?>
    <section class="featured-tutors">
      <h2 class="section-title">Tutor Quick Actions</h2>
      <div class="tutor-carousel">
        <article class="tutor-card quick-action-card"><strong class="tutor-name">Manage your tutoring</strong><div class="tutor-tags"><span class="tag">Profile</span><span class="tag">Slots</span><span class="tag">Requests</span><span class="tag">Reviews</span></div><a class="btn-book" href="<?= e(url('tutor-bookings')) ?>">Booking requests</a></article>
        <article class="tutor-card quick-action-card"><strong class="tutor-name">Availability &amp; profile</strong><p class="tutor-cred">Keep your subjects, experience, and available schedules updated.</p><a class="btn-book" href="<?= e(url('tutor-slots')) ?>">Manage slots</a></article>
      </div>
    </section>
  <?php else: ?>
    <section class="featured-tutors">
      <h2 class="section-title">Admin Quick Actions</h2>
      <div class="tutor-carousel">
        <article class="tutor-card quick-action-card"><strong class="tutor-name">Manage users</strong><a class="btn-book" href="<?= e(url('admin-users')) ?>">Open users</a></article>
        <article class="tutor-card quick-action-card"><strong class="tutor-name">Tutor verification</strong><a class="btn-book" href="<?= e(url('admin-verify-tutors')) ?>">Review tutors</a></article>
      </div>
    </section>
  <?php endif; ?>
</main>
