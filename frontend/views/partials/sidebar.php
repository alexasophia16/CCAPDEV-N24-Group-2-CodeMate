<?php
// [page, label, icon]
$navLinks = [
    'tutee' => [
        ['dashboard', 'Dashboard', '&#9679;'],
        ['find-tutor', 'Find a Tutor', '&#9678;'],
        ['my-bookings', 'My Bookings', '&#9636;'],
        ['discussions', 'Discussions', '&#9671;'],
        ['messages', 'Messages', '&#9993;'],
        ['resources', 'Resources', '&#9638;'],
        ['reports', 'Reports', '&#9873;'],
    ],
    'tutor' => [
        ['dashboard', 'Dashboard', '&#9679;'],
        ['tutor-profile-manage', 'My Tutor Profile', '&#9680;'],
        ['tutor-slots', 'Tutoring Slots', '&#9719;'],
        ['tutor-bookings', 'Booking Requests', '&#9635;'],
        ['tutor-reviews', 'Reviews', '&#9733;'],
        ['discussions', 'Discussions', '&#9671;'],
        ['messages', 'Messages', '&#9993;'],
        ['resources', 'Resources', '&#9638;'],
        ['reports', 'Reports', '&#9873;'],
    ],
    'admin' => [
        ['dashboard', 'Dashboard', '&#9679;'],
        ['admin-verify-tutors', 'Verify Tutors', '&#10003;'],
        ['admin-course-management', 'Course Management', '&#9637;'],
        ['admin-moderation', 'Content Moderation', '&#9873;'],
        ['admin-reports', 'Reports', '&#9650;'],
        ['admin-users', 'User Management', '&#9672;'],
        ['discussions', 'Discussions', '&#9671;'],
        ['resources', 'Resources', '&#9638;'],
    ],
];

// pages without a menu link highlight their parent
$parentPage = [
    'discussions-thread' => 'discussions',
    'tutor-profile'      => 'find-tutor',
    'book-consultation'  => 'find-tutor',
];
$highlight = $parentPage[$activePage] ?? $activePage;
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-mark">CM</div>
    <div class="sidebar-logo-text">
      <span class="sidebar-title">CodeMate</span>
      <span class="sidebar-subtitle">CCS - DLSU</span>
    </div>
    <button type="button" class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Toggle sidebar">
      &#10094;
    </button>
  </div>

  <nav class="sidebar-nav">
    <ul class="sidebar-list">
      <?php foreach ($navLinks[$currentUser['role']] as [$page, $label, $icon]): ?>
        <li class="sidebar-item">
          <a href="<?= e(url($page)) ?>" class="sidebar-link<?= $page === $highlight ? ' active' : '' ?>">
            <span class="sidebar-icon"><?= $icon ?></span>
            <span><?= e($label) ?></span>
            <?php if ($page === 'messages' && $unreadConversations > 0): ?>
              <span class="sidebar-badge"><?= $unreadConversations ?></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="sidebar-foot">CCAPDEV &middot; Group 2<br />CodeMate</div>
</aside>
