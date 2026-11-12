<?php
// Book a Consultation page. Variables come from BookingController::create():
// $tutor, $slots (open upcoming schedules), $selectedSlot (pre-selected slot id, or 0)
?>
<main class="booking-content">
  <?php if (!$slots): ?>
    <div class="modal">
      <h2 class="modal-title">Book a consultation</h2>
      <p class="modal-tutor">with <?= e($tutor['full_name']) ?></p>
      <p>This tutor has no open schedules right now. Please check again later or try another tutor.</p>
      <div class="actions">
        <a class="btn btn-cancel" href="<?= e(url('find-tutor')) ?>">Find another tutor</a>
      </div>
    </div>
  <?php else: ?>
    <?php // POST form: it creates a booking, handled by BookingController::store() ?>
    <form class="modal" method="post" action="<?= e(url('book-consultation')) ?>">
      
      <input type="hidden" name="tutor_id" value="<?= (int) $tutor['user_id'] ?>" />

      <h2 class="modal-title">Book a consultation</h2>
      <p class="modal-tutor">with <?= e($tutor['full_name']) ?></p>

      <div class="field">
        <label for="slot">Schedule</label>
        <select id="slot" name="slot_id" required>
          <option value="">Choose an open schedule</option>
          <?php foreach ($slots as $slot): ?>
            <option value="<?= (int) $slot['slot_id'] ?>" <?= (int) $slot['slot_id'] === $selectedSlot ? 'selected' : '' ?>>
              <?= e(schedule_label($slot['slot_date'], $slot['start_time'], $slot['end_time'])) ?>
              &middot; <?= e($slot['course_code']) ?> &middot; <?= e($slot['mode']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="notes">What do you need help with? (optional)</label>
        <textarea id="notes" name="notes" maxlength="500" placeholder="e.g. AVL tree rotations for the long exam"></textarea>
        <p class="field-hint">Up to 500 characters.</p>
      </div>

      <div class="actions">
        <a class="btn btn-cancel" href="<?= e(url('tutor-profile', ['id' => $tutor['user_id']])) ?>">Cancel</a>
        <button type="submit" class="btn btn-confirm">Confirm booking</button>
      </div>
    </form>
  <?php endif; ?>
</main>
