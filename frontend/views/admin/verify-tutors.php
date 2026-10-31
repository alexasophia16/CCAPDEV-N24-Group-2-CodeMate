<main class="role-page">
  <div class="role-page-header"><div><h1>Verify Tutors</h1><p>Review tutor applications before applicants can be presented as verified tutors.</p></div></div>
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
