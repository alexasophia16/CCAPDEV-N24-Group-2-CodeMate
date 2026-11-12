<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Tutoring Slots</h1>
      <p>Create and manage the schedules students can request.</p>
    </div>
  </div>
  
  <div class="role-card">
    <form id="slot-form" method="POST" action="index.php?page=tutor-slots">
      <div class="role-grid">
        <div class="role-field">
          <label>Course</label>
          <select name="course_id" id="course" required>
              <option value="" disabled selected>Select a course...</option>
              <?php if (!empty($courses)): ?>
                  <?php foreach ($courses as $course): ?>
                      <option value="<?= htmlspecialchars($course['course_id']) ?>"><?= htmlspecialchars($course['course_code']) ?></option>
                  <?php endforeach; ?>
              <?php endif; ?>
          </select>
        </div>
        <div class="role-field">
          <label>Date</label>
          <input type="date" name="slot_date" id="date" min="<?= date('Y-m-d') ?>" required />
        </div>
        <div class="role-field">
          <label>Start</label>
          <input type="time" name="start_time" id="start" required />
        </div>
        <div class="role-field">
          <label>End</label>
          <input type="time" name="end_time" id="end" required />
        </div>
        <div class="role-field">
          <label>Mode</label>
          <select name="mode" required>
              <option value="Online">Online</option>
              <option value="Face-to-face">Face-to-face</option>
          </select>
        </div>
      </div>
      <button type="submit" class="role-btn primary">Add slot</button>
    </form>
  </div>

  <div class="role-table-wrap" style="margin-top: 18px">
    <table class="role-table">
      <thead>
        <tr>
          <th>Course</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="rows">
        <?php if (empty($slots)): ?>
            <tr><td colspan="5" style="padding: 20px; text-align: center; color: var(--ink-faint);">No slots available.</td></tr>
        <?php else: ?>
            <?php foreach ($slots as $slot): ?>
                <tr>
                    <td><?= htmlspecialchars($slot['course_code'] ?? $slot['course_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($slot['slot_date']) ?></td>
                    <td><?= htmlspecialchars(date('H:i', strtotime($slot['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($slot['end_time']))) ?></td>
                    <td>
                        <span class="status-pill <?= $slot['status'] === 'open' ? 'status-confirmed' : 'status-pending' ?>">
                            <?= htmlspecialchars($slot['status']) ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <?php if ($slot['status'] === 'open'): ?>
                        <form method="POST" action="index.php?page=tutor-slot-delete" style="display:inline; margin:0;">
                            <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                            <button type="submit" class="role-btn primary" style="background: var(--red); border-color: var(--red);">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>