<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle ?? 'CodeMate - Reports') ?></title>

    <script>
      (function () {
        const savedTheme = localStorage.getItem("codemate_theme") || sessionStorage.getItem("codemate_theme");
        if (savedTheme === "dark") {
          document.documentElement.classList.add("dark");
          document.documentElement.setAttribute("data-theme", "dark");
        }
      })();
    </script>

    <link rel="stylesheet" href="<?= url('css/base.css') ?>" />
    <link rel="stylesheet" href="<?= url('css/sidebar.css') ?>" />
    <link rel="stylesheet" href="<?= url('css/topbar.css') ?>" />
    <link rel="stylesheet" href="<?= url('css/reports.css') ?>" />
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
        <button
          type="button"
          class="sidebar-collapse-btn"
          id="sidebar-collapse-btn"
          title="Toggle sidebar"
        >
          &#10094;
        </button>
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
            <a href="<?= url('reports') ?>" class="sidebar-link active">
              <span class="sidebar-icon">&#9873;</span>
              <span>Reports</span>
            </a>
          </li>
          
          <!-- Admin Links -->
          <li class="sidebar-item sidebar-item-admin">
            <a href="<?= url('admin/verify-tutors') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#10003;</span>
              <span>Verify Tutors</span>
            </a>
          </li>
          <li class="sidebar-item sidebar-item-admin">
            <a href="<?= url('admin/courses') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9637;</span>
              <span>Course Management</span>
            </a>
          </li>
          <li class="sidebar-item sidebar-item-admin">
            <a href="<?= url('admin/moderation') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9873;</span>
              <span>Content Moderation</span>
            </a>
          </li>
          <li class="sidebar-item sidebar-item-admin">
            <a href="<?= url('admin/reports') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9650;</span>
              <span>Admin Reports</span>
            </a>
          </li>
          <li class="sidebar-item sidebar-item-admin">
            <a href="<?= url('admin/users') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9672;</span>
              <span>User Management</span>
            </a>
          </li>
        </ul>
      </nav>

      <div class="sidebar-foot">CCAPDEV &middot; Group 2<br />CodeMate prototype</div>
    </aside>

    <div class="main-column">
      <header class="topbar">
        <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">
          &#9776;
        </button>

        <form class="topbar-search" role="search" id="topbar-search-form" action="<?= url('search') ?>" method="GET">
          <span class="topbar-search-icon">&#8981;</span>
          <input
            type="search"
            id="global-search"
            name="q"
            placeholder="Search tutors, subjects, discussions..."
          />
        </form>

        <div class="topbar-actions">
          <button type="button" class="icon-btn" id="btn-notifications" aria-label="Notifications">
            <span>&#128276;</span>
            <span class="icon-btn-badge" id="notifications-badge" hidden></span>
          </button>

          <button type="button" class="icon-btn" id="btn-bookmarks" aria-label="Toggle theme">
            <span class="theme-icon">&#127769;</span>
          </button>

          <div class="user-menu">
            <button
              type="button"
              class="user-menu-trigger"
              id="user-menu-trigger"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <span class="user-avatar" id="user-avatar"></span>
            </button>

            <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
              <div class="user-menu-identity">
                <span class="user-menu-dot" id="user-menu-dot"></span>
                <span class="user-menu-name-role" id="user-menu-name-role"></span>
              </div>
              <a href="<?= url('logout') ?>" class="user-menu-item user-menu-signout" id="btn-signout"
                >Signout</a
              >
            </div>
          </div>
        </div>
      </header>

      <main class="reports-layout">
        
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

        <div class="reports-header-row">
          <div>
            <h1>Reports</h1>
            <p>Review reports submitted to the moderation team.</p>
          </div>
          <button type="button" class="btn-file-report" id="btn-open-modal">+ File a report</button>
        </div>

        <div class="reports-card">
          <table class="reports-table">
            <thead>
              <tr>
                <th>REPORTER</th>
                <th>TARGET</th>
                <th>REASON</th>
                <th>STATUS</th>
              </tr>
            </thead>
            <tbody id="rows">
              <?php if (empty($reports)): ?>
                <tr>
                  <td colspan="4" style="text-align: center; color: var(--ink-faint); padding: 32px">
                    You haven't submitted any reports yet.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reports as $report): ?>
                  <tr>
                    <td><?= e($report['reporter']) ?></td>
                    <td><?= e($report['target']) ?></td>
                    <td><?= e($report['reason']) ?></td>
                    <td>
                      <span class="role-pill <?= $report['status'] === 'pending' ? 'pending' : '' ?>">
                        <?= e(ucfirst($report['status'])) ?>
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

    <!-- Report Form Modal -->
    <div class="modal-backdrop" id="modal-backdrop" hidden>
      <div class="report-modal">
        <h3>File a Report</h3>
        <form id="file-report-form" method="POST" action="<?= url('store-report') ?>">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
          
          <div class="form-group">
            <label for="target-input">Target</label>
            <input
              type="text"
              name="target"
              id="target-input"
              placeholder="e.g. Sample Tutor or Post #203"
              required
            />
          </div>
          <div class="form-group">
            <label for="reason-input">Reason</label>
            <textarea
              name="reason"
              id="reason-input"
              rows="4"
              placeholder="Describe the issue..."
              required
            ></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" id="btn-close-modal">Cancel</button>
            <button type="submit" class="btn-submit">Submit</button>
          </div>
        </form>
      </div>
    </div>

    <script src="<?= url('js/session.js') ?>"></script>
    <script src="<?= url('js/sidebar.js') ?>"></script>
    <script src="<?= url('js/topbar.js') ?>"></script>
    <script src="<?= url('js/reports.js') ?>"></script>
  </body>
</html>
