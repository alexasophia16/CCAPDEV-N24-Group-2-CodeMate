<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Booking Requests</h1>
      <p>Accept, decline, and manage student consultation requests.</p>
    </div>
  </div>
  <div class="role-table-wrap" style="background: white; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
    <table class="role-table" style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-align: left;">
        <tr>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">STUDENT</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">SUBJECT</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">SCHEDULE</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">STATUS</th>
          <th style="padding: 14px 20px; font-size: 12px; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #6b7280;">No booking requests received.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 16px 20px; color: #374151; font-size: 14px;"><?= htmlspecialchars($booking['student_name'] ?? 'Student') ?></td>
                    <td style="padding: 16px 20px; color: #374151; font-size: 14px;"><?= htmlspecialchars($booking['course_name'] ?? $booking['course_code'] ?? 'Subject') ?></td>
                    <td style="padding: 16px 20px; color: #374151; font-size: 14px;">
                        <?= htmlspecialchars(date('m/d/Y, g:i:s A', strtotime($booking['slot_date'] . ' ' . $booking['start_time']))) ?>
                    </td>
                    <td style="padding: 16px 20px;">
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600; 
                            <?php 
                                if ($booking['status'] === 'confirmed') echo 'background-color: #d1fae5; color: #065f46;';
                                elseif ($booking['status'] === 'pending') echo 'background-color: #fef3c7; color: #92400e;';
                                else echo 'background-color: #fee2e2; color: #991b1b;';
                            ?>">
                            <?= htmlspecialchars($booking['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 16px 20px;">
                        <?php if ($booking['status'] === 'pending'): ?>
                            <div style="display: flex; gap: 8px;">
                                <form method="POST" action="index.php?page=tutor-booking-update" style="margin: 0;">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" style="background: #1f4e3d; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Accept</button>
                                </form>
                                <form method="POST" action="index.php?page=tutor-booking-update" style="margin: 0;">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" style="background: #b91c1c; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Decline</button>
                                </form>
                            </div>
                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                            <form method="POST" action="index.php?page=tutor-booking-update" style="margin: 0;">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" style="background: #f3f4f6; color: #374151; padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Mark completed</button>
                            </form>
                        <?php else: ?>
                            <span style="color: #9b9b9b;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>