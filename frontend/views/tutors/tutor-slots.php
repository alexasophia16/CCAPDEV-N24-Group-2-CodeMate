<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Tutoring Slots</h1>
      <p>Create and manage the schedules students can request.</p>
    </div>
  </div>
  
   <div class="profile-card" style="margin-bottom: 24px;">
    <form method="POST" action="index.php?page=tutor-slots" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
      <div style="flex: 2; min-width: 200px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">Course</label>
        <select name="course_id" required style="width: 100%; padding: 10px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; font-size: 14px;">
            <option value="" disabled selected>Select a course...</option>
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['course_id']) ?>"><?= htmlspecialchars($course['course_code']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
      </div>
      <div style="flex: 1.5; min-width: 150px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">Date</label>
        <input type="date" name="slot_date" required style="width: 100%; padding: 10px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; font-size: 14px;">
      </div>
      <div style="flex: 1; min-width: 120px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">Start</label>
        <input type="time" name="start_time" required style="width: 100%; padding: 10px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; font-size: 14px;">
      </div>
      <div style="flex: 1; min-width: 120px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">End</label>
        <input type="time" name="end_time" required style="width: 100%; padding: 10px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; font-size: 14px;">
      </div>
      <div style="margin-top: 10px;">
        <button type="submit" class="booking-btn primary">Add slot</button>
      </div>
    </form>
  </div>

  <div style="margin-bottom: 16px;">
    <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">Mode</label>
    <select name="mode" required style="width: 100%; padding: 10px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; font-size: 14px;">
        <option value="Online">Online</option>
        <option value="Face-to-face">Face-to-face</option>
    </select>
  </div>

  <div class="bookings-table-wrap">
    <table class="bookings-table">
      <thead>
        <tr>
          <th>COURSE</th>
          <th>DATE</th>
          <th>TIME</th>
          <th>STATUS</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($slots)): ?>
            <tr><td colspan="5" style="padding: 20px; text-align: center; color: var(--ink-faint);">No slots available.</td></tr>
        <?php else: ?>
            <?php foreach ($slots as $slot): ?>
                <tr>
                    <td data-label="COURSE"><?= htmlspecialchars($slot['course_code'] ?? $slot['course_name'] ?? 'N/A') ?></td>
                    <td data-label="DATE"><?= htmlspecialchars($slot['slot_date']) ?></td>
                    <td data-label="TIME"><?= htmlspecialchars(date('H:i', strtotime($slot['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($slot['end_time']))) ?></td>
                    <td data-label="STATUS">
                        <span class="status-pill <?= $slot['status'] === 'open' ? 'status-confirmed' : 'status-pending' ?>">
                            <?= htmlspecialchars($slot['status']) ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <form method="POST" action="index.php?page=tutor-slot-delete" style="display:inline; margin:0;">
                            <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                            <button type="submit" class="booking-btn danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>