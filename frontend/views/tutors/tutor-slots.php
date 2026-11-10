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
        <select name="course_id" class="form-control" required style="width: 100%; font-size: 14px;">
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
        <input type="date" name="slot_date" class="form-control" required style="width: 100%; font-size: 14px;">
      </div>
      
      <div style="flex: 1; min-width: 120px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">Start</label>
        <input type="time" name="start_time" class="form-control" required style="width: 100%; font-size: 14px;">
      </div>
      
      <div style="flex: 1; min-width: 120px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">End</label>
        <input type="time" name="end_time" class="form-control" required style="width: 100%; font-size: 14px;">
      </div>
      
      <div style="margin-top: 10px;">
        <button type="submit" style="background: #1f4e3d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Add slot</button>
      </div>
      
    </form>
  </div>

  <div style="margin-bottom: 16px;">
    <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600;">Mode</label>
    <select name="mode" class="form-control" required style="width: 100%; font-size: 14px;">
        <option value="Online">Online</option>
        <option value="Face-to-face">Face-to-face</option>
    </select>
  </div>

  <div class="role-table-wrap profile-card" style="overflow: hidden; padding: 0;">
    <table class="role-table" style="width: 100%; border-collapse: collapse;">
      <thead style="text-align: left; border-bottom: 1px solid var(--line);">
        <tr>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">COURSE</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">DATE</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">TIME</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">STATUS</th>
          <th style="padding: 14px 20px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($slots)): ?>
            <tr><td colspan="5" style="padding: 20px; text-align: center; color: var(--ink-faint);">No slots available.</td></tr>
        <?php else: ?>
            <?php foreach ($slots as $slot): ?>
                <tr style="border-bottom: 1px solid var(--line);">
                    <td style="padding: 16px 20px; font-size: 14px;"><?= htmlspecialchars($slot['course_code'] ?? $slot['course_name'] ?? 'N/A') ?></td>
                    <td style="padding: 16px 20px; font-size: 14px;"><?= htmlspecialchars($slot['slot_date']) ?></td>
                    <td style="padding: 16px 20px; font-size: 14px;"><?= htmlspecialchars(date('H:i', strtotime($slot['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($slot['end_time']))) ?></td>
                    <td style="padding: 16px 20px;">
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; 
                            <?= $slot['status'] === 'open' ? 'background: #d1fae5; color: #065f46;' : 'background: #fef3c7; color: #92400e;' ?>">
                            <?= htmlspecialchars($slot['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 16px 20px; text-align: right;">
                        <form method="POST" action="index.php?page=tutor-slot-delete" style="display:inline; margin:0;">
                            <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                            <button type="submit" style="background: #b91c1c; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>