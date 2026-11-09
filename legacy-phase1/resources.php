<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle ?? 'CodeMate - Resources') ?></title>
    <link rel="stylesheet" href="<?= url('css/base.css') ?>" />
    <link rel="stylesheet" href="<?= url('css/sidebar.css') ?>" />
    <link rel="stylesheet" href="<?= url('css/topbar.css') ?>" />
    <link rel="stylesheet" href="<?= url('css/role-pages.css') ?>" />
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
          <li class="sidebar-item">
            <a href="<?= url('dashboard') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9679;</span>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="<?= url('find-tutor') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9678;</span>
              <span>Find a Tutor</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="<?= url('my-bookings') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9636;</span>
              <span>My Bookings</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="<?= url('discussions') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9671;</span>
              <span>Discussions</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="<?= url('messages') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9993;</span>
              <span>Messages</span>
              <span class="sidebar-badge" id="sidebar-messages-badge" hidden></span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="<?= url('reports') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9873;</span>
              <span>Reports</span>
            </a>
          </li>
        </ul>
      </nav>
      <div class="sidebar-foot">CCAPDEV &middot; Group 2<br />CodeMate prototype</div>
    </aside>

    <div class="main-column">
      <header class="topbar">
        <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">&#9776;</button>
        <form class="topbar-search" role="search" id="topbar-search-form" action="<?= url('search') ?>" method="GET">
          <span class="topbar-search-icon">&#8981;</span>
          <input type="search" id="global-search" name="q" placeholder="Search tutors, subjects, discussions..." />
        </form>
        <div class="topbar-actions">
          <button type="button" class="icon-btn" id="btn-notifications" aria-label="Notifications">
            <span>&#128276;</span>
            <span class="icon-btn-badge" id="notifications-badge" hidden></span>
          </button>
          <button type="button" class="icon-btn" id="btn-bookmarks" aria-label="Saved tutors">
            <span>&#9733;</span>
          </button>
          <div class="user-menu">
            <button type="button" class="user-menu-trigger" id="user-menu-trigger" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar" id="user-avatar"></span>
            </button>
            <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
              <div class="user-menu-identity">
                <span class="user-menu-dot" id="user-menu-dot"></span>
                <span class="user-menu-name-role" id="user-menu-name-role"></span>
              </div>
              <a href="<?= url('logout') ?>" class="user-menu-item user-menu-signout" id="btn-signout">Signout</a>
            </div>
          </div>
        </div>
      </header>
      
      <main class="role-page">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 20px; padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 6px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #dcfce7; color: #166534; border-radius: 6px;">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <div class="role-page-header">
          <div>
            <h1>Study Resources</h1>
            <p>Student-made tutoring materials organized by course.</p>
          </div>
          <?php if ($currentUser['role'] === 'tutor'): ?>
            <button class="role-btn primary" id="upload-btn">Upload Resource</button>
          <?php endif; ?>
        </div>
        
        <div class="role-grid" id="resource-list">
          <?php if (empty($resources)): ?>
            <p style="color: var(--ink-faint);">No resources available right now.</p>
          <?php else: ?>
            <?php foreach ($resources as $res): ?>
              <div class="role-card">
                <span class="role-pill"><?= e($res['course']) ?></span>
                <?php if ($res['status'] === 'pending'): ?>
                   <span class="role-pill" style="background: #fef08a; color: #854d0e; margin-left: 8px;">Pending</span>
                <?php endif; ?>
                <h2 style="margin-top:8px"><?= e($res['title']) ?></h2>
                <p><?= e($res['type']) ?> &middot; <?= e($res['author']) ?></p>
                <button class="role-btn secondary" style="margin-top:10px">Preview</button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($currentUser['role'] === 'tutor'): ?>
        <div class="role-card" id="upload-card" hidden style="margin-top: 18px">
          <h2>Upload Resource</h2>
          <form id="upload-form" method="POST" action="<?= url('store-resource') ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
            <div class="role-field"><label>Title</label><input name="title" id="title" required /></div>
            <div class="role-field"><label>Course</label><input name="course" id="course" required /></div>
            <div class="role-field">
              <label>Type</label>
              <select name="type" id="type">
                <option value="PDF">PDF</option>
                <option value="Video">Video</option>
                <option value="Notes">Notes</option>
              </select>
            </div>
            <div class="role-field">
              <label>File (front-end prototype)</label><input type="file" name="file" />
            </div>
            <button type="submit" class="role-btn primary">Submit for moderation</button>
          </form>
        </div>
        <?php endif; ?>
      </main>
    </div>
    
    <script src="<?= url('js/session.js') ?>"></script>
    <script src="<?= url('js/sidebar.js') ?>"></script>
    <script src="<?= url('js/topbar.js') ?>"></script>
    <script src="<?= url('js/resources.js') ?>"></script>
  </body>
</html>
