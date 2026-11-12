<?php
// My Bookings page. Variables come from BookingController::myBookings():
// $bookings, $status (the current filter, '' for all)
?>
<main class="bookings-content">
  <div class="page-header">
    <h1>My Bookings</h1>
    <p class="page-subtitle">Consultations you reserved with a tutor.</p>
  </div>

  <?php // GET form: filtering only reads data, so it uses ?page=my-bookings&status=... ?>
  <form class="bookings-filter" method="get" action="index.php">
    <input type="hidden" name="page" value="my-bookings" />
    <label for="status-filter">Show</label>
    <select id="status-filter" name="status">
      <option value="">All bookings</option>
      <?php foreach (Booking::STATUSES as $option): ?>
        <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="booking-btn">Filter</button>
  </form>

  <?php if (!$bookings): ?>
    <p class="bookings-message">
      <?= $status === '' ? 'You have no bookings yet.' : 'No ' . e($status) . ' bookings.' ?>
      <a href="<?= e(url('find-tutor')) ?>">Find a tutor</a>
    </p>
  <?php else: ?>
    <div class="bookings-table-wrap">
      <table class="bookings-table">
        <thead>
          <tr>
            <th>Tutor</th>
            <th>Subject</th>
            <th>Schedule</th>
            <th>Mode</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $booking): ?>
            <tr>
              <td data-label="Tutor">
                <a class="tutor-link" href="<?= e(url('tutor-profile', ['id' => $booking['tutor_id']])) ?>">
                  <?= e($booking['tutor_name']) ?>
                </a>
              </td>
              <td data-label="Subject">
                <?= e($booking['course_name']) ?>
                <?php if ($booking['notes']): ?>
                  <div class="booking-notes"><?= e($booking['notes']) ?></div>
                <?php endif; ?>
              </td>
              <td data-label="Schedule">
                <?= e(schedule_label($booking['slot_date'], $booking['start_time'], $booking['end_time'])) ?>
              </td>
              <td data-label="Mode"><?= e($booking['mode']) ?></td>
              <td data-label="Status">
                <span class="status-pill status-<?= e($booking['status']) ?>"><?= e($booking['status']) ?></span>
                <?php if ($booking['status'] === 'pending'): ?>
                  <div class="booking-notes">Waiting for admin approval</div>
                <?php endif; ?>
              </td>
              <td data-label="Action">
                <?php // Which action shows depends on the booking:
                      // upcoming pending/confirmed -> Cancel, completed + reviewed -> stars,
                      // completed without review -> review form, anything else -> a dash ?>
                <?php if ($booking['is_upcoming'] && in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                  <?php // class "confirm-cancel" makes my-bookings.js ask "Cancel this booking?" first ?>
                  <form method="post" action="<?= e(url('booking-cancel')) ?>" class="confirm-cancel">
                    
                    <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>" />
                    <button type="submit" class="booking-btn danger">Cancel</button>
                  </form>
                <?php elseif ($booking['status'] === 'completed' && $booking['review_rating'] !== null): ?>
                  <span class="review-given">
                    <?= str_repeat('&#9733;', (int) $booking['review_rating']) ?> Reviewed
                  </span>
                <?php elseif ($booking['status'] === 'completed'): ?>
                  <?php // <details> shows/hides the review form when clicked, no JavaScript needed ?>
                  <details class="review-toggle">
                    <summary>Leave a review</summary>
                    <form method="post" action="<?= e(url('booking-review')) ?>" class="review-form">
                      
                      <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>" />
                      <select name="rating" required aria-label="Rating">
                        <option value="">Rating</option>
                        <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                          <option value="<?= $stars ?>"><?= $stars ?> star<?= $stars === 1 ? '' : 's' ?></option>
                        <?php endfor; ?>
                      </select>
                      <textarea name="comment" maxlength="1000" placeholder="How was the session? (optional)"></textarea>
                      <button type="submit" class="booking-btn primary">Submit review</button>
                    </form>
                  </details>
                <?php else: ?>
                  &mdash;
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
