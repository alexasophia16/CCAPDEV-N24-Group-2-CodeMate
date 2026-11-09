<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - My Tutor Profile</title>
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
        <button type="button" class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Toggle sidebar">&#10094;</button>
      </div>

      <nav class="sidebar-nav">
        <ul class="sidebar-list">
          <li class="sidebar-item"><a href="/dashboard" class="sidebar-link active"><span class="sidebar-icon">&#9679;</span><span>Dashboard</span></a></li>
          <li class="sidebar-item"><a href="/find-tutor" class="sidebar-link"><span class="sidebar-icon">&#9678;</span><span>Find a Tutor</span></a></li>
          <li class="sidebar-item"><a href="/my-bookings" class="sidebar-link"><span class="sidebar-icon">&#9636;</span><span>My Bookings</span></a></li>
          <li class="sidebar-item"><a href="/discussions" class="sidebar-link"><span class="sidebar-icon">&#9671;</span><span>Discussions</span></a></li>
          <li class="sidebar-item"><a href="/messages" class="sidebar-link"><span class="sidebar-icon">&#9993;</span><span>Messages</span><span class="sidebar-badge" id="sidebar-messages-badge" hidden></span></a></li>
          <li class="sidebar-item"><a href="/reports" class="sidebar-link"><span class="sidebar-icon">&#9873;</span><span>Reports</span></a></li>
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
            <h1>My Tutor Profile</h1>
            <p>Manage the profile students see when choosing a tutor.</p>
          </div>
        </div>
        <div class="role-grid">
          <form class="role-card" id="profile-form" method="POST" action="/tutor-profile-manage">
            <h2>Profile Information</h2>
            
            <div class="role-field">
                <label>Bio</label>
                <textarea name="bio" id="bio" rows="4" required><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="role-field">
                <label>Experience</label>
                <textarea name="experience" id="experience" rows="4" required><?= htmlspecialchars($profile['experience'] ?? '') ?></textarea>
            </div>
            
            <!-- Courses managed via DB relationships; text input hidden or made read-only for safety unless you add tutor_courses pivot logic -->
            
            <button type="submit" class="role-btn primary">Save changes</button>
          </form>
          
          <div class="role-card">
            <h2>Verification & Credibility</h2>
            <div id="credibility" style="margin-top: 15px;">
                <p><strong>Status:</strong> 
                    <span style="color: <?= ($profile['verification_status'] ?? '') === 'verified' ? '#065f46' : '#92400e' ?>; font-weight: bold;">
                        <?= ucfirst(htmlspecialchars($profile['verification_status'] ?? 'pending')) ?>
                    </span>
                </p>
                <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
                    Verified tutors appear higher in search results and can accept bookings without administrative review.
                </p>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="/js/session.js"></script>
    <script src="/js/sidebar.js"></script>
    <script src="/js/topbar.js"></script>
  </body>
</html>