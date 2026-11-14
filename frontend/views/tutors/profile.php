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
        <h1><?= e($tutor['full_name']) ?></h1>
        <div class="tutor-meta">
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
