<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Booking Requests</h1>
      <p>Accept, decline, and manage student consultation requests.</p>
    </div>
  </div>
  <div class="role-table-wrap">
    <table class="role-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Subject</th>
          <th>Schedule</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="rows">
        <?php if (empty($bookings)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">No booking requests received.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['student_name']) ?></td>
                    <td><?= htmlspecialchars($booking['course_code']) ?></td>
                    <td>
                        <?= htmlspecialchars(date('M d, Y', strtotime($booking['slot_date']))) ?><br>
                        <small style="color: #666;"><?= htmlspecialchars(date('h:i A', strtotime($booking['start_time']))) ?> - <?= htmlspecialchars(date('h:i A', strtotime($booking['end_time']))) ?></small>
                    </td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85em; background-color: <?= $booking['status'] === 'confirmed' ? '#d1fae5' : ($booking['status'] === 'pending' ? '#fef3c7' : '#fee2e2') ?>; color: <?= $booking['status'] === 'confirmed' ? '#065f46' : ($booking['status'] === 'pending' ? '#92400e' : '#991b1b') ?>;">
                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($booking['status'] === 'pending'): ?>
                            <form method="POST" action="/tutor-booking-update" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="role-btn primary" style="padding: 4px 10px; font-size: 0.85em; margin-right: 4px;">Accept</button>
                            </form>
                            <form method="POST" action="/tutor-booking-update" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="role-btn" style="padding: 4px 10px; font-size: 0.85em; background: #fee2e2; color: #991b1b; border: none;">Decline</button>
                            </form>
                        <?php else: ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>