<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Tutoring Slots</h1>
      <p>Create and manage the schedules students can request.</p>
    </div>
  </div>
  
  <div class="role-card">
    <form id="slot-form" method="POST" action="/tutor-slots">
      <div class="role-grid">
        
        <div class="role-field">
          <label>Course</label>
          <select name="course_id" id="course" required>
              <option value="">Select a course...</option>
              <?php if (!empty($courses)): ?>
                  <?php foreach ($courses as $course): ?>
                      <option value="<?= htmlspecialchars($course['course_id']) ?>">
                          <?= htmlspecialchars($course['course_code']) ?>
                      </option>
                  <?php endforeach; ?>
              <?php endif; ?>
          </select>
        </div>

        <div class="role-field">
          <label>Date</label>
          <input name="slot_date" id="date" type="date" required />
        </div>
        <div class="role-field">
          <label>Start</label>
          <input name="start_time" id="start" type="time" required />
        </div>
        <div class="role-field">
          <label>End</label>
          <input name="end_time" id="end" type="time" required />
        </div>
      </div>
      
      <input type="hidden" name="mode" value="Online">
      
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
        </tr>
      </thead>
      <tbody id="rows">
          <?php if (empty($slots)): ?>
              <tr>
                  <td colspan="4" style="text-align: center; padding: 20px;">You have no slots scheduled.</td>
              </tr>
          <?php else: ?>
              <?php foreach ($slots as $slot): ?>
                  <tr>
                      <td><?= htmlspecialchars($slot['course_code']) ?></td>
                      <td><?= htmlspecialchars(date('M d, Y', strtotime($slot['slot_date']))) ?></td>
                      <td>
                          <?= htmlspecialchars(date('h:i A', strtotime($slot['start_time']))) ?> - 
                          <?= htmlspecialchars(date('h:i A', strtotime($slot['end_time']))) ?>
                      </td>
                      <td>
                          <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85em; background-color: <?= $slot['status'] === 'open' ? '#d1fae5' : '#fef3c7' ?>; color: <?= $slot['status'] === 'open' ? '#065f46' : '#92400e' ?>;">
                              <?= ucfirst(htmlspecialchars($slot['status'])) ?>
                          </span>
                      </td>
                  </tr>
              <?php endforeach; ?>
          <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>