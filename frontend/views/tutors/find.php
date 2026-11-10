<?php
// Find a Tutor page. Variables come from TutorController::find():
// $tutors, $courses, $query, $courseId, $sort, $canBook
?>
<main class="page-content">
  <div class="page-header">
    <h1>Find a Tutor</h1>
    <p class="page-subtitle">Browse verified peer tutors across CCS courses.</p>
  </div>

  <?php // GET form: submitting it reloads this page with ?page=find-tutor&q=...&course=...&sort=... ?>
  <form class="filter-row" method="get" action="index.php" role="search">
    <?php // keeps the router on this page, since the form replaces the whole query string ?>
    <input type="hidden" name="page" value="find-tutor" />
    <input
      type="search"
      name="q"
      value="<?= e($query) ?>"
      placeholder="Search by tutor name or course"
      aria-label="Search tutors"
    />
    <select name="course" aria-label="Filter by course">
      <option value="">All courses</option>
      <?php foreach ($courses as $course): ?>
        <?php // "selected" keeps the dropdown on the course the user filtered by ?>
        <option value="<?= (int) $course['course_id'] ?>" <?= (int) $course['course_id'] === $courseId ? 'selected' : '' ?>>
          <?= e($course['course_code'] . ' - ' . $course['course_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="sort" aria-label="Sort tutors">
      <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Sort: Highest Rating</option>
      <option value="sessions" <?= $sort === 'sessions' ? 'selected' : '' ?>>Sort: Most Sessions</option>
      <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Sort: Name (A-Z)</option>
    </select>
    <button type="submit" class="filter-btn">Search</button>
    <?php if ($query !== '' || $courseId > 0): ?>
      <a class="filter-clear" href="<?= e(url('find-tutor')) ?>">Clear</a>
    <?php endif; ?>
  </form>

  <?php if ($query !== '' || $courseId > 0): ?>
    <p class="results-count">
      <?= count($tutors) ?> tutor<?= count($tutors) === 1 ? '' : 's' ?> found<?= $query !== '' ? ' for "' . e($query) . '"' : '' ?>
    </p>
  <?php endif; ?>

  <?php if (!$tutors): ?>
    <p class="empty-message">No tutors match your search.</p>
  <?php else: ?>
    <div class="tutor-grid">
      <?php foreach ($tutors as $tutor): ?>
        <article class="tutor-card">
          <div class="tutor-top">
            <div class="tutor-avatar"><?= e(initials($tutor['full_name'])) ?></div>
            <div class="tutor-info">
              <a class="tutor-name" href="<?= e(url('tutor-profile', ['id' => $tutor['user_id']])) ?>">
                <?= e($tutor['full_name']) ?>
              </a>
              <div class="tutor-meta">
                <?php // tutors with no reviews yet show "New" instead of a number ?>
                <span class="tutor-cred">
                  &#9733; <?= $tutor['rating'] === null ? 'New' : e($tutor['rating']) ?>
                  &middot; <?= (int) $tutor['sessions'] ?> session<?= (int) $tutor['sessions'] === 1 ? '' : 's' ?>
                </span>
                <span class="tutor-badge verified">Verified &#10003;</span>
              </div>
            </div>
          </div>

          <div class="tutor-tags">
            <?php foreach ($tutor['courses'] as $courseName): ?>
              <span class="subject-pill"><?= e($courseName) ?></span>
            <?php endforeach; ?>
          </div>

          <div class="card-actions">
            <a class="mail-btn" href="<?= e(url('messages')) ?>" title="Message tutor">&#9993;&#xFE0E;</a>
            <?php // only tutees can book; tutors and admins get a link to the profile instead ?>
            <?php if ($canBook): ?>
              <a class="btn-book" href="<?= e(url('book-consultation', ['tutor' => $tutor['user_id']])) ?>">Book session</a>
            <?php else: ?>
              <a class="btn-book" href="<?= e(url('tutor-profile', ['id' => $tutor['user_id']])) ?>">View profile</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
