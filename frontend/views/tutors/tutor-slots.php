<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Tutoring Slots</h1>
      <p>Create and manage the schedules students can request.</p>
    </div>
  </div>
  
  <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
    <form method="POST" action="index.php?page=tutor-slots" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
      <div style="flex: 2; min-width: 200px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #374151;">Course</label>
        <select name="course_id" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
            <option value="" disabled selected>Select a course...</option>
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['course_id']) ?>"><?= htmlspecialchars($course['course_code']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
      </div>
      <div style="flex: 1.5; min-width: 150px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #374151;">Date</label>
        <input type="date" name="slot_date" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; color: #374151;">
      </div>
      <div style="flex: 1; min-width: 120px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #374151;">Start</label>
        <input type="time" name="start_time" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; color: #374151;">
      </div>
      <div style="flex: 1; min-width: 120px;">
        <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #374151;">End</label>
        <input type="time" name="end_time" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; color: #374151;">
      </div>
      <div style="margin-top: 10px;">
        <button type="submit" style="background: #1f4e3d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Add slot</button>
      </div>
    </form>
  </div>

  <div style="flex: 1; min-width: 140px;">
  <label style="display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #374151;">Mode</label>
  <select name="mode" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
      <option value="Online">Online</option>
      <option value="Face-to-face">Face-to-face</option>
  </select>
</div>

  <div class="role-table-wrap" style="background: white; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden;">
    <table class="role-table" style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-align: left;">
        <tr>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">COURSE</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">DATE</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">TIME</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">STATUS</th>
          <th style="padding: 14px 20px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($slots)): ?>
            <tr><td colspan="5" style="padding: 20px; text-align: center; color: #6b7280;">No slots available.</td></tr>
        <?php else: ?>
            <?php foreach ($slots as $slot): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 16px 20px; color: #374151; font-size: 14px;"><?= htmlspecialchars($slot['course_code'] ?? $slot['course_name'] ?? 'N/A') ?></td>
                    <td style="padding: 16px 20px; color: #374151; font-size: 14px;"><?= htmlspecialchars($slot['slot_date']) ?></td>
                    <td style="padding: 16px 20px; color: #374151; font-size: 14px;"><?= htmlspecialchars(date('H:i', strtotime($slot['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($slot['end_time']))) ?></td>
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