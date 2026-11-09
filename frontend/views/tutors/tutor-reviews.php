<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - Reviews</title>
    <link rel="stylesheet" href="/css/base.css" />
    <link rel="stylesheet" href="/css/sidebar.css" />
    <link rel="stylesheet" href="/css/topbar.css" />
    <link rel="stylesheet" href="/css/role-pages.css" />
  </head>
  <body class="app-layout">
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
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
          <li class="sidebar-item"><a href="/dashboard" class="sidebar-link active"><span class="sidebar-icon">&#9679;</span><span>Dashboard</span></a></li>
          <li class="sidebar-item"><a href="/find-tutor" class="sidebar-link"><span class="sidebar-icon">&#9678;</span><span>Find a Tutor</span></a></li>
          <li class="sidebar-item"><a href="/my-bookings" class="sidebar-link"><span class="sidebar-icon">&#9636;</span><span>My Bookings</span></a></li>
          <li class="sidebar-item"><a href="/discussions" class="sidebar-link"><span class="sidebar-icon">&#9671;</span><span>Discussions</span></a></li>
          <li class="sidebar-item"><a href="/messages" class="sidebar-link"><span class="sidebar-icon">&#9993;</span><span>Messages</span><span class="sidebar-badge" id="sidebar-messages-badge" hidden></span></a></li>
          <li class="sidebar-item"><a href="/reports" class="sidebar-link"><span class="sidebar-icon">&#9873;</span><span>Reports</span></a></li>
          <!-- Admin links hidden for tutor view -->
        </ul>
      </nav>

      <div class="sidebar-foot">CCAPDEV &middot; Group 2<br />CodeMate prototype</div>
    </aside>
    <div class="main-column">
      <header class="topbar">
        <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">&#9776;</button>
        <form class="topbar-search" role="search" id="topbar-search-form">
          <span class="topbar-search-icon">&#8981;</span>
          <input type="search" id="global-search" name="q" placeholder="Search tutors, subjects, discussions..." />
        </form>
        <div class="topbar-actions">
          <button type="button" class="icon-btn" id="btn-notifications" aria-label="Notifications">
            <span>&#128276;</span><span class="icon-btn-badge" id="notifications-badge" hidden></span>
          </button>
          <button type="button" class="icon-btn" id="btn-bookmarks" aria-label="Saved tutors"><span>&#9733;</span></button>
          <div class="user-menu">
            <button type="button" class="user-menu-trigger" id="user-menu-trigger" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar" id="user-avatar"></span>
            </button>
            <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
              <div class="user-menu-identity">
                <span class="user-menu-dot" id="user-menu-dot"></span>
                <span class="user-menu-name-role" id="user-menu-name-role"></span>
              </div>
              <a href="/login" class="user-menu-item user-menu-signout" id="btn-signout">Signout</a>
            </div>
          </div>
        </div>
      </header>
      <main class="role-page">
        <div class="role-page-header">
          <div>
            <h1>Reviews</h1>
            <p>Feedback submitted after completed tutoring sessions.</p>
          </div>
        </div>
        
        <!-- Summary Card populated by PHP -->
        <div class="role-card" id="summary" style="display: flex; gap: 20px; align-items: center; padding: 20px;">
            <div style="font-size: 2.5em; font-weight: bold; color: #f59e0b;">
                <?= $averageRating > 0 ? number_format($averageRating, 1) : '0.0' ?> <span style="font-size: 0.6em;">&#9733;</span>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.2em;">Average Rating</h3>
                <p style="margin: 0; color: #666;">Based on <?= $totalReviews ?> student review<?= $totalReviews !== 1 ? 's' : '' ?></p>
            </div>
        </div>

        <!-- Dynamic Review List -->
        <div id="review-list" style="margin-top: 24px;">
            <?php if (empty($reviews)): ?>
                <div class="role-card" style="text-align: center; color: #666; padding: 30px;">
                    No reviews yet. Complete more sessions to receive feedback!
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="role-card" style="margin-bottom: 16px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <div>
                                <h4 style="margin: 0; font-size: 1.1em;"><?= htmlspecialchars($review['reviewer']) ?></h4>
                                <small style="color: #666;"><?= htmlspecialchars($review['course_name']) ?> &middot; <?= date('M d, Y', strtotime($review['created_at'])) ?></small>
                            </div>
                            <div style="color: #f59e0b; font-weight: bold;">
                                <?= htmlspecialchars($review['rating']) ?> / 5.0
                            </div>
                        </div>
                        <p style="margin: 0; line-height: 1.5; color: #333;">
                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
      </main>
    </div>
    <script src="/js/session.js"></script>
    <script src="/js/sidebar.js"></script>
    <script src="/js/topbar.js"></script>
  </body>
</html>