<?php
// Tutor Profile page. Variables come from TutorController::profile():
// $tutor, $slots (open upcoming schedules), $reviews, $canBook
?>
<main class="page-content profile-page">
  <p class="back-link"><a href="<?= e(url('find-tutor')) ?>">&larr; Back to Find a Tutor</a></p>

  <section class="profile-card">
    <div class="profile-head">
      <div class="tutor-avatar avatar-large"><?= e(initials($tutor['full_name'])) ?></div>
      <div>
        <div style="display: flex; align-items: center; gap: 12px;">
          <h1 style="margin: 0;"><?= e($tutor['full_name']) ?></h1>
          <!-- REPORT BUTTON: directly next to the name -->
          <button type="button" class="btn-report-text btn-open-report-modal" style="font-size: 12px; color: var(--ink-faint); cursor: pointer; border: none; background: none;" data-target-type="User" data-target-id="<?= $tutor['user_id'] ?>">Report</button>
        </div>
        <div class="tutor-meta" style="margin-top: 4px;">
          <span class="tutor-cred">
            &#9733; <?= $tutor['rating'] === null ? 'No ratings yet' : e($tutor['rating']) ?>
            &middot; <?= (int) $tutor['sessions'] ?> completed session<?= (int) $tutor['sessions'] === 1 ? '' : 's' ?>
          </span>
          <span class="tutor-badge verified">Verified &#10003;</span>
        </div>
      </div>
    </div>
    <p class="note">Ratings come from students who completed a session with this tutor.</p>
    <div class="card-actions profile-actions">
      <a class="mail-btn" href="<?= e(url('messages')) ?>" title="Message tutor">&#9993;&#xFE0E;</a>
      <?php if ($canBook): ?>
        <a class="btn-book" href="<?= e(url('book-consultation', ['tutor' => $tutor['user_id']])) ?>">Book session</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="info-card">
    <h2>About</h2>
    <p><?= e($tutor['bio'] ?: 'No bio yet.') ?></p>
  </section>

  <section class="info-card">
    <h2>Tutoring experience</h2>
    <p><?= e($tutor['experience'] ?: 'Not provided.') ?></p>
  </section>

  <section class="info-card">
    <h2>Subjects they tutor</h2>
    <div class="tutor-tags tutor-tags-row">
      <?php 
        $subjectArray = array_map('trim', explode(',', $tutor['subjects'] ?? ''));
        foreach ($subjectArray as $subjectName): 
            if ($subjectName !== ''):
      ?>
        <span class="subject-pill"><?= e($subjectName) ?></span>
      <?php 
            endif;
        endforeach; 
      ?>
    </div>
  </section>

  <section class="info-card">
    <h2>Open schedule</h2>
    <ul class="slot-list">
      <?php if (!$slots): ?>
        <li>No open slots right now.</li>
      <?php endif; ?>
      <?php foreach ($slots as $slot): ?>
        <li>
          <span class="slot-day"><?= e(date('D, M j', strtotime($slot['slot_date']))) ?></span>
          <span class="slot-time">
            <?= e(date('g:i A', strtotime($slot['start_time'])) . ' – ' . date('g:i A', strtotime($slot['end_time']))) ?>
            &middot; <?= e($slot['course_code']) ?> &middot; <?= e($slot['mode']) ?>
          </span>
          <?php // links to the booking form with this slot already selected ?>
          <?php if ($canBook): ?>
            <a href="<?= e(url('book-consultation', ['tutor' => $tutor['user_id'], 'slot' => $slot['slot_id']])) ?>">Book</a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="info-card">
    <h2>Student reviews</h2>
    <?php if (!$reviews): ?>
      <p class="review-summary">No reviews yet.</p>
    <?php else: ?>
      <p class="review-summary">
        <?= e($tutor['rating']) ?> out of 5 from <?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?>
      </p>
      <?php foreach ($reviews as $review): ?>
        <article class="review">
          <div class="review-stars">
            <?php // filled stars for the rating, empty stars for the rest of the 5 ?>
            <?= str_repeat('&#9733;', (int) $review['rating']) . str_repeat('&#9734;', 5 - (int) $review['rating']) ?>
          </div>
          <?php if ($review['comment'] !== null && $review['comment'] !== ''): ?>
            <p class="review-comment"><?= e($review['comment']) ?></p>
          <?php endif; ?>
          <div class="review-meta">
            <?= e($review['reviewer']) ?> &middot; <?= e($review['course_name']) ?>
            &middot; <?= e(date('M j, Y', strtotime($review['created_at']))) ?>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<!-- Report Form Modal  -->
<div class="modal-backdrop" id="report-modal-backdrop" hidden>
  <div class="report-modal">
    <h3>File a Report</h3>
    <form id="file-report-form" method="POST" action="<?= url('store-report') ?>">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
      
      <!-- HIDDEN INPUTS (Auto-filled by JS) -->
      <input type="hidden" name="target_id" id="report-target-id" value="" required />
      <input type="hidden" name="target_type" id="report-target-type" value="" required />
      
      <div class="form-group">
        <label>You are reporting a <strong id="display-target-type">...</strong></label>
      </div>
      
      <div class="form-group">
        <label for="reason-input">Reason</label>
        <input type="text" name="reason" id="reason-input" maxlength="255" placeholder="e.g. Inappropriate content" required />
      </div>
      <div class="form-group">
        <label for="details-input">Details (optional)</label>
        <textarea name="details" id="details-input" rows="4" maxlength="1000" placeholder="Describe the issue..."></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="btn-close-report-modal">Cancel</button>
        <button type="submit" class="btn-submit">Submit</button>
      </div>
    </form>
  </div>
</div>

<script>
// Find all report buttons on the page
const reportButtons = document.querySelectorAll('.btn-open-report-modal');
const reportModal = document.getElementById('report-modal-backdrop');
const closeReportBtn = document.getElementById('btn-close-report-modal');

// The hidden inputs inside the form
const inputTargetId = document.getElementById('report-target-id');
const inputTargetType = document.getElementById('report-target-type');
const displayTargetType = document.getElementById('display-target-type');

// Attach click event to every report button
reportButtons.forEach(button => {
  button.addEventListener('click', (e) => {
    // Grab the ID and Type from the button that was clicked
    const targetId = button.getAttribute('data-target-id');
    const targetType = button.getAttribute('data-target-type');
    
    // Inject them into the hidden form inputs
    inputTargetId.value = targetId;
    inputTargetType.value = targetType;
    displayTargetType.textContent = targetType;
    
    // Show the modal
    reportModal.removeAttribute('hidden');
  });
});

// Close modal logic
if (closeReportBtn) {
  closeReportBtn.addEventListener('click', () => {
    reportModal.setAttribute('hidden', 'true');
  });
}
reportModal.addEventListener('click', (e) => {
  if (e.target === reportModal) {
    reportModal.setAttribute('hidden', 'true');
  }
});
</script>
