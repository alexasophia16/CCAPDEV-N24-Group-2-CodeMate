<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - Booking Requests</title>
    <link rel="stylesheet" href="css/base.css" />
    <link rel="stylesheet" href="css/sidebar.css" />
    <link rel="stylesheet" href="css/topbar.css" />
    <link rel="stylesheet" href="css/role-pages.css" />
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
          <li class="sidebar-item"><a href="dashboard.html" class="sidebar-link active"><span class="sidebar-icon">&#9679;</span><span>Dashboard</span></a></li>
          <li class="sidebar-item"><a href="find-tutor.html" class="sidebar-link"><span class="sidebar-icon">&#9678;</span><span>Find a Tutor</span></a></li>
          <li class="sidebar-item"><a href="my-bookings.html" class="sidebar-link"><span class="sidebar-icon">&#9636;</span><span>My Bookings</span></a></li>
          <li class="sidebar-item"><a href="discussions.html" class="sidebar-link"><span class="sidebar-icon">&#9671;</span><span>Discussions</span></a></li>
          <li class="sidebar-item"><a href="messages.html" class="sidebar-link"><span class="sidebar-icon">&#9993;</span><span>Messages</span><span class="sidebar-badge" id="sidebar-messages-badge" hidden></span></a></li>
          <li class="sidebar-item"><a href="reports.html" class="sidebar-link"><span class="sidebar-icon">&#9873;</span><span>Reports</span></a></li>
          <li class="sidebar-item sidebar-item-admin" hidden><a href="admin-verify-tutors.html" class="sidebar-link"><span class="sidebar-icon">&#9670;</span><span>Verify Tutors</span></a></li>
          <li class="sidebar-item sidebar-item-admin" hidden><a href="admin-course-management.html" class="sidebar-link"><span class="sidebar-icon">&#9670;</span><span>Course Management</span></a></li>
          <li class="sidebar-item sidebar-item-admin" hidden><a href="admin-moderation.html" class="sidebar-link"><span class="sidebar-icon">&#9670;</span><span>Content Moderation</span></a></li>
          <li class="sidebar-item sidebar-item-admin" hidden><a href="admin-reports.html" class="sidebar-link"><span class="sidebar-icon">&#9670;</span><span>Reports</span></a></li>
          <li class="sidebar-item sidebar-item-admin" hidden><a href="admin-users.html" class="sidebar-link"><span class="sidebar-icon">&#9670;</span><span>User Management</span></a></li>
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
              <a href="login.html" class="user-menu-item user-menu-signout" id="btn-signout">Signout</a>
            </div>
          </div>
        </div>
      </header>
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
    </div>
    <script src="js/session.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/topbar.js"></script>
  </body>
</html>