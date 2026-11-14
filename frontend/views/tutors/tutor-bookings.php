<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Booking Requests</h1>
      <p>New requests are approved or declined by an admin. Once confirmed, you can mark the session as completed.</p>
    </div>
  </div>
  <div class="role-table-wrap">
    <table class="role-table" style="width: 100%; border-collapse: collapse;">
      <thead style="text-align: left;">
        <tr>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">STUDENT</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">SUBJECT</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">SCHEDULE</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">STATUS</th>
          <th style="padding: 14px 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">ACTION</th>
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
                    <td style="padding: 16px 20px; font-size: 14px;"><?= htmlspecialchars($booking['student_name'] ?? 'Student') ?></td>
                    <td style="padding: 16px 20px; font-size: 14px;"><?= htmlspecialchars($booking['course_name'] ?? $booking['course_code'] ?? 'Subject') ?></td>
                    <td style="padding: 16px 20px; font-size: 14px;">
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
                            <span style="color: #92400e; font-size: 13px;">Awaiting admin approval</span>
                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                            <form method="POST" action="index.php?page=tutor-booking-update" style="margin: 0;">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>">
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