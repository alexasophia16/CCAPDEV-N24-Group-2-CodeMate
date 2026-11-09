<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - Discussions</title>

    <link rel="stylesheet" href="<?= url('styles/base.css') ?>" />
    <link rel="stylesheet" href="<?= url('styles/sidebar.css') ?>" />
    <link rel="stylesheet" href="<?= url('styles/topbar.css') ?>" />
    <link rel="stylesheet" href="<?= url('styles/discussions.css') ?>" />
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
            <a href="<?= url('discussions') ?>" class="sidebar-link active">
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
          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-verify-tutors') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Verify Tutors</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-course-management') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Course Management</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-moderation') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Content Moderation</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-reports') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Reports</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-users') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
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

        <form class="topbar-search" role="search" id="topbar-search-form">
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

          <button type="button" class="icon-btn" id="btn-bookmarks" aria-label="Saved tutors">
            <span>&#9733;</span>
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
              <a href="login.html" class="user-menu-item user-menu-signout" id="btn-signout"
                >Signout</a
              >
            </div>
          </div>
        </div>
      </header>

      <main class="discussions-content">
        <div class="discussions-header-row">
          <div>
            <h1>Discussions</h1>
            <p class="discussions-subtitle">Ask or Answer a Question.</p>
          </div>
          <button type="button" class="btn-create-post" id="btn-toggle-modal">
            + Ask Question
          </button>
        </div>

        <div class="course-tabs-wrapper">
          <a href="<?= url('discussions', ['course' => 'ALL']) ?>" 
          class="course-tab <?= $selectedFilter === 'ALL' ? 'active' : '' ?>">All Subjects</a>

          <?php foreach ($courses as $course): ?>
          <a href="<?= url('discussions', ['course' => $course['code']]) ?>" 
          class="course-tab <?= $selectedFilter === $course['code'] ? 'active' : '' ?>">
          <?= e($course['code']) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="threads-list" id="threads-container">
          <?php if (empty($threads)): ?>
            <p class="discussions-subtitle">No discussions found.</p>
          <?php else: ?>
            <?php foreach ($threads as $thread): ?>
              <div class="thread-preview-card">
                <div class="thread-card-top">
                  <span class="thread-badge-blue"><?= e($thread['course']) ?></span>
                </div>
                <div class="thread-card-main">
                  <h3 class="thread-title">
                    <a href="<?= url('discussions-thread', ['id' => $thread['id']]) ?>"><?= e($thread['title']) ?></a>
                  </h3>
                  <p class="thread-snippet"><?= e(mb_strimwidth($thread['content'], 0, 140, '...')) ?></p>
                  <div class="thread-author">
                    <div class="thread-avatar"><?= e(initials($thread['author'])) ?></div>
                    <div>
                      <span class="author-name"><?= e($thread['author']) ?></span>
                      <span class="author-role"><?= e(role_label($thread['authorRole'])) ?> &middot; <?= time_ago($thread['created_at']) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </main>
    </div>

    <div class="modal-overlay hidden" id="post-modal">
      <div class="modal-box">
        <div class="modal-top">
          <h2>Post a Discussion Question</h2>
          <button type="button" class="btn-close-modal" id="btn-close-modal">&times;</button>
        </div>
        <form id="new-post-form" action="<?= url('store-discussion') ?>" method="POST">
    
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
    
    <div class="form-group">
        <label for="post-course">Course Subject</label>
        <select id="post-course" name="course" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?= e($course['code']) ?>"><?= e($course['code']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
      <label for="post-title">Question Title</label>
      <input type="text" id="post-title" name="title" placeholder="What is your question?" required />
    </div>

    <div class="form-group">
      <label for="post-content">Details</label>
      <textarea id="post-content" name="content" rows="5" placeholder="Elaborate on the issue or topic you need help with" required></textarea>
    </div>
    
    <div class="modal-actions">
        <button type="button" class="btn-cancel" id="btn-cancel-post">Cancel</button>
        <button type="submit" class="btn-submit">Publish Question</button>
    </div>
</form>
      </div>
    </div>

    <script src="scripts/session.js"></script>
    <script src="scripts/sidebar.js"></script>
    <script src="scripts/topbar.js"></script>
    <script src="scripts/discussions.js"></script>
  </body>
</html>
