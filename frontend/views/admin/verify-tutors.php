<?php // Variables come from AdminUserController::verifyTutors(): $bookings (pending booking requests), $tutors (pending tutor applications) ?>
<main class="role-page">
  <div class="role-page-header"><div><h1>Verify Tutors</h1><p>Approve or decline tutee booking requests, and review tutor applications before applicants can be presented as verified tutors.</p></div></div>

  <h2 style="font-size:17px;margin-bottom:12px;">Booking requests</h2>
  <div class="role-table-wrap">
    <table class="role-table">
      <thead><tr><th>Tutee</th><th>Tutor</th><th>Subject</th><th>Schedule</th><th>Requested</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (!$bookings): ?><tr><td colspan="6">There are no pending booking requests.</td></tr><?php endif; ?>
      <?php foreach ($bookings as $booking): ?>
        <tr>
          <td><strong><?= e($booking['tutee_name']) ?></strong><br><small><?= e($booking['tutee_email']) ?></small><?php if ($booking['notes']): ?><br><small>Notes: <?= e($booking['notes']) ?></small><?php endif; ?></td>
          <td><?= e($booking['tutor_name']) ?></td>
          <td><?= e($booking['course_code']) ?> - <?= e($booking['course_name']) ?></td>
          <td><?= e(schedule_label($booking['slot_date'], $booking['start_time'], $booking['end_time'])) ?><br><small><?= e($booking['mode']) ?></small></td>
          <td><?= e(date('M j, Y', strtotime($booking['created_at']))) ?></td>
          <td><div class="role-actions">
            <?php if ($booking['is_upcoming']): ?>
            <form method="post" action="<?= e(url('admin-review-booking')) ?>">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" /><input type="hidden" name="booking_id" value="<?= (int)$booking['booking_id'] ?>" /><input type="hidden" name="decision" value="confirmed" />
              <button class="role-btn primary" type="submit">Approve</button>
            </form>
            <?php else: ?>
            <small>Schedule already started</small>
            <?php endif; ?>
            <form method="post" action="<?= e(url('admin-review-booking')) ?>">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" /><input type="hidden" name="booking_id" value="<?= (int)$booking['booking_id'] ?>" /><input type="hidden" name="decision" value="declined" />
              <button class="role-btn danger" type="submit">Decline</button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h2 style="font-size:17px;margin:28px 0 12px;">Tutor applications</h2>
  <div class="role-table-wrap">
    <table class="role-table">
      <thead><tr><th>Tutor</th><th>Subjects</th><th>Experience</th><th>Applied</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (!$tutors): ?><tr><td colspan="5">There are no pending tutor applications.</td></tr><?php endif; ?>
      <?php foreach ($tutors as $tutor): ?>
        <tr>
          <td><strong><?= e($tutor['full_name']) ?></strong><br><small><?= e($tutor['email']) ?></small><br><small><?= e($tutor['bio'] ?? '') ?></small></td>
          <td><?= e($tutor['subjects'] ?: 'No subjects selected') ?></td>
          <td><?= e($tutor['experience'] ?: 'Not provided') ?></td>
          <td><?= e(date('M j, Y', strtotime($tutor['applied_at']))) ?></td>
          <td><div class="role-actions">
            <form method="post" action="<?= e(url('admin-review-tutor')) ?>">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" /><input type="hidden" name="tutor_id" value="<?= (int)$tutor['user_id'] ?>" /><input type="hidden" name="decision" value="verified" />
              <button class="role-btn primary" type="submit">Approve</button>
            </form>
            <form method="post" action="<?= e(url('admin-review-tutor')) ?>">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" /><input type="hidden" name="tutor_id" value="<?= (int)$tutor['user_id'] ?>" /><input type="hidden" name="decision" value="rejected" />
              <button class="role-btn danger" type="submit">Reject</button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
