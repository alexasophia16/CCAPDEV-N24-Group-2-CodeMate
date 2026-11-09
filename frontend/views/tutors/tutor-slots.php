<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - Tutoring Slots</title>
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
          <li class="sidebar-item"><a href="mreports.html" class="sidebar-link"><span class="sidebar-icon">&#9873;</span><span>Reports</span></a></li>
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
            <h1>Tutoring Slots</h1>
            <p>Create and manage the schedules students can request.</p>
          </div>
        </div>
        
        <div class="role-card">
          <form id="slot-form" method="POST" action="/tutor-slots">
            <div class="role-grid">
              
              <div class="role-field">
                <label>Course</label>
                <select name="course_id" id="course" required>
                    <option value="">Select a course...</option>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= htmlspecialchars($course['course_id']) ?>">
                                <?= htmlspecialchars($course['course_code']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
              </div>

              <div class="role-field">
                <label>Date</label>
                <input name="slot_date" id="date" type="date" required />
              </div>
              <div class="role-field">
                <label>Start</label>
                <input name="start_time" id="start" type="time" required />
              </div>
              <div class="role-field">
                <label>End</label>
                <input name="end_time" id="end" type="time" required />
              </div>
            </div>
            
            <input type="hidden" name="mode" value="Online">
            
            <button type="submit" class="role-btn primary">Add slot</button>
          </form>
        </div>

        <div class="role-table-wrap" style="margin-top: 18px">
          <table class="role-table">
            <thead>
              <tr>
                <th>Course</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="rows">
                <?php if (empty($slots)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">You have no slots scheduled.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($slots as $slot): ?>
                        <tr>
                            <td><?= htmlspecialchars($slot['course_code']) ?></td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($slot['slot_date']))) ?></td>
                            <td>
                                <?= htmlspecialchars(date('h:i A', strtotime($slot['start_time']))) ?> - 
                                <?= htmlspecialchars(date('h:i A', strtotime($slot['end_time']))) ?>
                            </td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85em; background-color: <?= $slot['status'] === 'open' ? '#d1fae5' : '#fef3c7' ?>; color: <?= $slot['status'] === 'open' ? '#065f46' : '#92400e' ?>;">
                                    <?= ucfirst(htmlspecialchars($slot['status'])) ?>
                                </span>
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